<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Controller;

use Exception;
use OnMoon\OpenApiServerBundle\Event\Server\RequestDtoEvent;
use OnMoon\OpenApiServerBundle\Event\Server\RequestEvent;
use OnMoon\OpenApiServerBundle\Event\Server\ResponseDtoEvent;
use OnMoon\OpenApiServerBundle\Event\Server\ResponseEvent;
use OnMoon\OpenApiServerBundle\Exception\ApiCallFailed;
use OnMoon\OpenApiServerBundle\Interfaces\ApiLoader;
use OnMoon\OpenApiServerBundle\Interfaces\Dto;
use OnMoon\OpenApiServerBundle\Interfaces\GetResponseCode;
use OnMoon\OpenApiServerBundle\Interfaces\RequestHandler;
use OnMoon\OpenApiServerBundle\Interfaces\SetClientIp;
use OnMoon\OpenApiServerBundle\Interfaces\SetRequest;
use OnMoon\OpenApiServerBundle\Router\RouteLoader;
use OnMoon\OpenApiServerBundle\Serializer\DtoSerializer;
use OnMoon\OpenApiServerBundle\Specification\Definitions\Operation;
use OnMoon\OpenApiServerBundle\Specification\Definitions\Specification;
use OnMoon\OpenApiServerBundle\Specification\SpecificationLoader;
use OnMoon\OpenApiServerBundle\Validator\RequestSchemaValidator;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

use function count;
use function get_class;
use function in_array;
use function intdiv;
use function is_numeric;
use function ltrim;
use function Safe\sprintf;
use function strcasecmp;

use const JSON_THROW_ON_ERROR;
use const JSON_UNESCAPED_UNICODE;

final class ApiController
{
    private ?ApiLoader $apiLoader = null;
    private SpecificationLoader $specificationLoader;
    private RouterInterface $router;
    private DtoSerializer $serializer;
    private EventDispatcherInterface $eventDispatcher;
    private RequestSchemaValidator $requestValidator;

    public function __construct(
        SpecificationLoader $specificationLoader,
        RouterInterface $router,
        DtoSerializer $serializer,
        EventDispatcherInterface $eventDispatcher,
        RequestSchemaValidator $requestValidator
    ) {
        $this->specificationLoader = $specificationLoader;
        $this->router              = $router;
        $this->serializer          = $serializer;
        $this->eventDispatcher     = $eventDispatcher;
        $this->requestValidator    = $requestValidator;
    }

    public function setApiLoader(ApiLoader $loader): void
    {
        $this->apiLoader = $loader;
    }

    public function handle(Request $request): Response
    {
        $route         = $this->getRoute($request);
        $operationId   = (string) $route->getOption(RouteLoader::OPENAPI_OPERATION);
        $specification = $this->getSpecification($route);
        $operation     = $specification->getOperation($operationId);

        $this->eventDispatcher->dispatch(new RequestEvent($request, $operationId, $specification));
        $this->requestValidator->validate($request, $specification, $operationId);

        [$requestHandlerInterface, $requestHandler] = $this->getRequestHandler($request, $operation);
        [$methodName, $inputDtoClass]               = $this->getMethodAndInputDtoFQCN($requestHandlerInterface);

        $requestDto = null;

        if ($inputDtoClass !== null) {
            $requestDto = $this->createRequestDto($request, $operation, $inputDtoClass);
        }

        $this->eventDispatcher->dispatch(new RequestDtoEvent($requestDto, $operationId, $specification, $requestHandler, $request));

        $responseDto = $this->executeRequestHandler($requestHandler, $methodName, $requestDto);
        $this->eventDispatcher->dispatch(new ResponseDtoEvent($responseDto, $operationId, $specification));

        $response = $this->createResponse($requestHandler, $operation, $requestHandlerInterface, $responseDto);
        $this->eventDispatcher->dispatch(new ResponseEvent($response, $operationId, $specification, $requestHandler, $request));

        return $response;
    }

    private function getSpecificationName(Route $route): string
    {
        return (string) $route->getOption(RouteLoader::OPENAPI_SPEC);
    }

    private function getSpecification(Route $route): Specification
    {
        return $this->specificationLoader->load($this->getSpecificationName($route));
    }

    /** @psalm-param class-string<Dto> $inputDtoClass */
    private function createRequestDto(
        Request $request,
        Operation $operation,
        string $inputDtoClass
    ): Dto {
        return $this->serializer->createRequestDto(
            $request,
            $operation,
            $inputDtoClass
        );
    }

    private function executeRequestHandler(
        RequestHandler $requestHandler,
        string $methodName,
        ?Dto $requestDto
    ): ?Dto {
        /** @var Dto|null $responseDto */
        $responseDto = $requestDto !== null ?
            $requestHandler->{$methodName}($requestDto) :
            $requestHandler->{$methodName}();

        return $responseDto;
    }

    /** @return array{0: class-string<RequestHandler>, 1: RequestHandler} */
    private function getRequestHandler(Request $request, Operation $operation): array
    {
        $handlerName = $operation->getRequestHandlerName();

        $requestHandlers = $this->getApiLoader()::getSubscribedServices();
        /** @var String $requestHandlerSubscribedString */
        $requestHandlerSubscribedString = $requestHandlers[$handlerName];
        /** @psalm-var class-string<RequestHandler> $requestHandlerInterface */
        $requestHandlerInterface = ltrim($requestHandlerSubscribedString, '?');
        $requestHandler          = $this->getApiLoader()->get($handlerName);

        if ($requestHandler === null) {
            throw ApiCallFailed::becauseNotImplemented($requestHandlerInterface);
        }

        if ($requestHandler instanceof SetRequest) {
            $requestHandler->setRequest($request);
        }

        if ($requestHandler instanceof SetClientIp) {
            $requestHandler->setClientIp((string) $request->getClientIp());
        }

        return [$requestHandlerInterface, $requestHandler];
    }

    private function createResponse(
        RequestHandler $requestHandler,
        Operation $operation,
        string $handlerInterface,
        ?Dto $responseDto = null
    ): Response {
        $response = new JsonResponse();
        $response->setEncodingOptions(JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

        $allowedCodes = $this->getApiLoader()->getAllowedCodes(
            $handlerInterface,
            $responseDto !== null ? get_class($responseDto) : 'void'
        );

        $guessedCode = null;
        if (count($allowedCodes) === 1 && is_numeric($allowedCodes[0])) {
            $guessedCode = (int) $allowedCodes[0];
        }

        if ($requestHandler instanceof GetResponseCode) {
            $statusCode = $requestHandler->getResponseCode($guessedCode) ?? $guessedCode;
        } else {
            $statusCode = $guessedCode;
        }

        if ($statusCode === null) {
            throw ApiCallFailed::becauseNoResponseCodeSet();
        }

        $matchedCode = $this->findMatchingResponseCode($statusCode, $allowedCodes);

        $response->setStatusCode($statusCode);

        if ($responseDto !== null) {
            $responseData = $this->serializer->createResponseFromDto($responseDto, $operation->getResponse($matchedCode)->getSchema());
            $response->setData($responseData);
        }

        return $response;
    }

    /** @param string[] $allowedCodes */
    private function findMatchingResponseCode(int $statusCode, array $allowedCodes): string
    {
        $code = (string) $statusCode;
        if (in_array($code, $allowedCodes, true)) {
            return $code;
        }

        $code = (string) intdiv($statusCode, 100) . 'XX';
        foreach ($allowedCodes as $allowedCode) {
            if (strcasecmp($code, $allowedCode) === 0) {
                return $allowedCode;
            }
        }

        if (in_array('default', $allowedCodes, true)) {
            return 'default';
        }

        throw ApiCallFailed::becauseWrongResponseCodeSet($allowedCodes);
    }

    private function getRoute(Request $request): Route
    {
        /** @var string $routeName */
        $routeName = $request->attributes->get('_route', '');
        $route     = $this->router->getRouteCollection()->get($routeName);

        if ($route === null) {
            throw new NotFoundHttpException('Route not found');
        }

        return $route;
    }

    /**
     * @psalm-param class-string<RequestHandler> $requestHandlerInterface
     *
     * @return array{0: string, 1: class-string<Dto>|null}
     */
    private function getMethodAndInputDtoFQCN(string $requestHandlerInterface): array
    {
        $interfaceReflectionClass = new ReflectionClass($requestHandlerInterface);
        $methods                  = $interfaceReflectionClass->getMethods(ReflectionMethod::IS_PUBLIC);
        if (count($methods) !== 1) {
            throw new Exception(
                sprintf(
                    '"%s" has %d public methods, exactly one expected',
                    $requestHandlerInterface,
                    count($methods)
                )
            );
        }

        $methodName       = $methods[0]->getName();
        $methodParameters = $methods[0]->getParameters();

        if (count($methodParameters) === 0) {
            return [$methodName, null];
        }

        $inputType = $methodParameters[0]->getType();
        if (! ($inputType instanceof ReflectionNamedType)) {
            throw new Exception('Input parameter for ' . $requestHandlerInterface . ' is not a named type');
        }

        /** @var class-string<Dto> $inputTypeName */
        $inputTypeName = $inputType->getName();

        return [$methodName, $inputTypeName];
    }

    private function getApiLoader(): ApiLoader
    {
        if ($this->apiLoader === null) {
            throw ApiCallFailed::becauseApiLoaderNotFound();
        }

        return $this->apiLoader;
    }
}
