<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Controller;

use cebe\openapi\spec\OpenApi;
use cebe\openapi\spec\Operation;
use cebe\openapi\spec\PathItem;
use OnMoon\OpenApiServerBundle\CodeGenerator\Naming\NamingStrategy;
use OnMoon\OpenApiServerBundle\Event\Server\RequestDtoEvent;
use OnMoon\OpenApiServerBundle\Event\Server\RequestEvent;
use OnMoon\OpenApiServerBundle\Event\Server\ResponseDtoEvent;
use OnMoon\OpenApiServerBundle\Event\Server\ResponseEvent;
use OnMoon\OpenApiServerBundle\Exception\ApiCallFailed;
use OnMoon\OpenApiServerBundle\Interfaces\ApiLoader;
use OnMoon\OpenApiServerBundle\Interfaces\Dto;
use OnMoon\OpenApiServerBundle\Interfaces\GetResponseCode;
use OnMoon\OpenApiServerBundle\Interfaces\RequestHandler;
use OnMoon\OpenApiServerBundle\Interfaces\ResponseDto;
use OnMoon\OpenApiServerBundle\Interfaces\SetClientIp;
use OnMoon\OpenApiServerBundle\Interfaces\SetRequest;
use OnMoon\OpenApiServerBundle\Router\RouteLoader;
use OnMoon\OpenApiServerBundle\Serializer\DtoSerializer;
use OnMoon\OpenApiServerBundle\Specification\SpecificationLoader;
use OnMoon\OpenApiServerBundle\Validator\RequestSchemaValidator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;
use const JSON_UNESCAPED_UNICODE;

class ApiController
{
    private ?ApiLoader $apiLoader = null;
    private SpecificationLoader $specificationLoader;
    private RouterInterface $router;
    private DtoSerializer $serializer;
    private NamingStrategy $namingStrategy;
    private EventDispatcherInterface $eventDispatcher;
    private RequestSchemaValidator $requestValidator;

    public function __construct(
        SpecificationLoader $specificationLoader,
        RouterInterface $router,
        DtoSerializer $serializer,
        NamingStrategy $namingStrategy,
        EventDispatcherInterface $eventDispatcher,
        RequestSchemaValidator $requestValidator
    ) {
        $this->specificationLoader = $specificationLoader;
        $this->router              = $router;
        $this->serializer          = $serializer;
        $this->namingStrategy      = $namingStrategy;
        $this->eventDispatcher     = $eventDispatcher;
        $this->requestValidator    = $requestValidator;
    }

    public function setApiLoader(ApiLoader $loader) : void
    {
        $this->apiLoader = $loader;
    }

    public function handle(Request $request) : Response
    {
        $route         = $this->getRoute($request);
        $path          = (string) $route->getOption(RouteLoader::OPENAPI_PATH);
        $method        = (string) $route->getOption(RouteLoader::OPENAPI_METHOD);
        $specification = $this->getSpecification($route);
        $operation     = $this->getOperation($specification, $path, $method);

        $this->eventDispatcher->dispatch(new RequestEvent($request, $operation, $path, $method));
        $this->requestValidator->validate($request, $specification, $path, $method);

        $requestDto = $this->createRequestDto($request, $route, $operation);
        $this->eventDispatcher->dispatch(new RequestDtoEvent($requestDto, $operation, $path, $method));

        $requestHandler = $this->getRequestHandler($request, $this->getRequestHandlerInterface($route, $operation));

        $responseDto = $this->executeRequestHandler($requestHandler, $operation, $requestDto);
        $this->eventDispatcher->dispatch(new ResponseDtoEvent($responseDto, $operation, $path, $method));

        $response = $this->createResponse($requestHandler, $responseDto);
        $this->eventDispatcher->dispatch(new ResponseEvent($response, $operation, $path, $method));

        return $response;
    }

    private function getSpecificationName(Route $route) : string
    {
        return (string) $route->getOption(RouteLoader::OPENAPI_SPEC);
    }

    private function getSpecification(Route $route) : OpenApi
    {
        return $this->specificationLoader->load($this->getSpecificationName($route));
    }

    /**
     * @psalm-return class-string<RequestHandler>
     */
    private function getRequestHandlerInterface(Route $route, Operation $operation) : string
    {
        $specificationName      = $this->getSpecificationName($route);
        $specificationNamespace = $this->specificationLoader->get($specificationName)->getNameSpace();

        return $this->namingStrategy->getInterfaceFQCN($specificationNamespace, $operation->operationId);
    }

    private function createRequestDto(
        Request $request,
        Route $route,
        Operation $operation
    ) : ?Dto {
        return $this->serializer->createRequestDto(
            $request,
            $route,
            $this->getRequestHandlerInterface($route, $operation),
            $this->namingStrategy->stringToMethodName($operation->operationId)
        );
    }

    private function executeRequestHandler(
        RequestHandler $requestHandler,
        Operation $operation,
        ?Dto $requestDto
    ) : ?ResponseDto {
        $requestHandlerMethodName = $this->namingStrategy->stringToMethodName($operation->operationId);

        /** @var ResponseDto|null $responseDto */
        $responseDto = $requestDto !== null ?
            $requestHandler->{$requestHandlerMethodName}($requestDto) :
            $requestHandler->{$requestHandlerMethodName}();

        return $responseDto;
    }

    private function getRequestHandler(Request $request, string $requestHandlerInterface) : RequestHandler
    {
        if ($this->apiLoader === null) {
            throw ApiCallFailed::becauseApiLoaderNotFound();
        }

        $requestHandler = $this->apiLoader->get($requestHandlerInterface);

        if ($requestHandler === null) {
            throw ApiCallFailed::becauseNotImplemented($requestHandlerInterface);
        }

        if ($requestHandler instanceof SetRequest) {
            $requestHandler->setRequest($request);
        }

        if ($requestHandler instanceof SetClientIp) {
            $requestHandler->setClientIp((string) $request->getClientIp());
        }

        return $requestHandler;
    }

    private function getOperation(OpenApi $specification, string $path, string $method) : Operation
    {
        /** @var PathItem $pathItem */
        $pathItem = $specification->paths[$path];
        /** @var Operation $operation */
        $operation = $pathItem->{$method};

        return $operation;
    }

    private function createResponse(RequestHandler $requestHandler, ?ResponseDto $responseDto = null) : Response
    {
        $response = new JsonResponse();
        $response->setEncodingOptions(JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

        $statusCode = null;

        if ($responseDto instanceof ResponseDto) {
            $response->setContent($this->serializer->createResponse($responseDto));
            $statusCode = $responseDto::_getResponseCode() ?? $statusCode;
        }

        if ($requestHandler instanceof GetResponseCode) {
            $statusCode = $requestHandler->getResponseCode($statusCode) ?? $statusCode;
        }

        $statusCode ??= Response::HTTP_OK;

        $response->setStatusCode($statusCode);

        return $response;
    }

    private function getRoute(Request $request) : Route
    {
        $routeName = (string) $request->attributes->get('_route', '');
        $route     = $this->router->getRouteCollection()->get($routeName);

        if ($route === null) {
            throw new NotFoundHttpException('Route not found');
        }

        return $route;
    }
}
