<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Controller;

use cebe\openapi\spec\Operation;
use cebe\openapi\spec\PathItem;
use Exception;
use League\OpenAPIValidation\PSR7\OperationAddress;
use League\OpenAPIValidation\PSR7\ValidatorBuilder;
use Nyholm\Psr7\Factory\Psr17Factory;
use OnMoon\OpenApiServerBundle\CodeGenerator\Naming\NamingStrategy;
use OnMoon\OpenApiServerBundle\Exception\ApiCallFailed;
use OnMoon\OpenApiServerBundle\Interfaces\ApiLoader;
use OnMoon\OpenApiServerBundle\Interfaces\ResponseDto;
use OnMoon\OpenApiServerBundle\Interfaces\SetClientIp;
use OnMoon\OpenApiServerBundle\Interfaces\SetRequest;
use OnMoon\OpenApiServerBundle\Router\RouteLoader;
use OnMoon\OpenApiServerBundle\Serializer\DtoSerializer;
use OnMoon\OpenApiServerBundle\Specification\SpecificationLoader;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;
use const JSON_UNESCAPED_UNICODE;

class ApiController
{
    private ?ApiLoader $apiLoader = null;

    public function setApiLoader(ApiLoader $loader) : void
    {
        $this->apiLoader = $loader;
    }

    public function handle(
        Request $request,
        RouterInterface $router,
        NamingStrategy $namingStrategy,
        DtoSerializer $serializer,
        SpecificationLoader $loader
    ) : Response {
        $routeName = (string) $request->attributes->get('_route', '');
        $route     = $router->getRouteCollection()->get($routeName);

        if ($route === null) {
            throw new Exception('Route not found');
        }

        $path      = (string) $route->getOption(RouteLoader::OPENAPI_PATH);
        $method    = (string) $route->getOption(RouteLoader::OPENAPI_METHOD);
        $specName  = (string) $route->getOption(RouteLoader::OPENAPI_SPEC);
        $nameSpace = $loader->get($specName)->getNameSpace();
        $spec      = $loader->load($specName);

        /** @var PathItem $pathItem */
        $pathItem = $spec->paths[$path];
        /** @var Operation $operation */
        $operation = $pathItem->{$method};

        $operationId = $operation->operationId;

        $psr17Factory = new Psr17Factory();

        (new ValidatorBuilder())
            ->fromSchema($spec)
            ->getRoutedRequestValidator()
            ->validate(
                new OperationAddress(
                    $path,
                    $method
                ),
                (new PsrHttpFactory($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory))
                    ->createRequest($request)
            );

        if ($this->apiLoader === null) {
            throw ApiCallFailed::becauseApiLoaderNotFound();
        }

        $apiInterface = $namingStrategy->getInterfaceFQCN($nameSpace, $operationId);
        $methodName   = $namingStrategy->stringToMethodName($operationId);

        $service = $this->apiLoader->get($apiInterface);

        if ($service === null) {
            throw ApiCallFailed::becauseNotImplemented($apiInterface);
        }

        if ($service instanceof SetRequest) {
            $service->setRequest($request);
        }

        if ($service instanceof SetClientIp) {
            $service->setClientIp((string) $request->getClientIp());
        }

        $requestDto = $serializer->createRequestDto($request, $route, $apiInterface, $methodName);
        /** @var ResponseDto|null $responseDto */
        $responseDto = $requestDto ? $service->{$methodName}($requestDto) : $service->{$methodName}();

        $response = new JsonResponse();
        $response->setEncodingOptions(JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

        if ($responseDto instanceof ResponseDto) {
            $response->setContent($serializer->createResponse($responseDto));
            $response->setStatusCode($responseDto::_getResponseCode());
        } else {
            $response->setStatusCode(200);
        }

        return $response;
    }
}
