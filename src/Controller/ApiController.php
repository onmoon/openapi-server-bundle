<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Controller;

use cebe\openapi\spec\OpenApi;
use OnMoon\OpenApiServerBundle\Interfaces\ApiLoader;
use OnMoon\OpenApiServerBundle\CodeGenerator\Naming\NamingStrategy;
use OnMoon\OpenApiServerBundle\Exception\ApiCallFailed;
use OnMoon\OpenApiServerBundle\Interfaces\SetClientIp;
use OnMoon\OpenApiServerBundle\Interfaces\SetRequest;
use OnMoon\OpenApiServerBundle\Router\RouteLoader;
use OnMoon\OpenApiServerBundle\Serializer\DtoSerializer;
use League\OpenAPIValidation\PSR7\OperationAddress;
use League\OpenAPIValidation\PSR7\ValidatorBuilder;
use Nyholm\Psr7\Factory\Psr17Factory;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use function is_object;
use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;
use const JSON_UNESCAPED_UNICODE;

class ApiController
{
    private ?ApiLoader $apiLoader = null;
    private DtoSerializer $serializer;

    public function setApiLoader(
        ApiLoader $loader,
        DtoSerializer $serializer
    ) {
        $this->apiLoader  = $loader;
        $this->serializer = $serializer;
    }

    public function handle(Request $request, RouterInterface $router, NamingStrategy $namingStrategy) {
        $routeName = $request->attributes->get('_route');
        $route = $router->getRouteCollection()->get($routeName);

        $path = $route->getOption(RouteLoader::OPENAPI_PATH);
        $method = $route->getOption(RouteLoader::OPENAPI_METHOD);
        /** @var OpenApi $spec $spec */
        $spec = $route->getOption(RouteLoader::OPENAPI_SPEC);
        $operationId = $spec->paths[$path]->{$method}->operationId;

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

        if(is_null($this->apiLoader)) {
            throw ApiCallFailed::becauseApiLoaderNotFound();
        }

        $apiInterface = $namingStrategy->getInterfaceFQCN($spec->info->title, $operationId);
        $methodName = $namingStrategy->stringToMethodName($operationId);

        $service = $this->apiLoader->get($apiInterface);

        if(is_null($service)) {
            throw ApiCallFailed::becauseNotImplemented($apiInterface);
        }

        if($service instanceof SetRequest) {
            $service->setRequest($request);
        }

        if($service instanceof SetClientIp) {
            $service->setClientIp($request->getClientIp());
        }

        $requestDto  = $this->serializer->createRequestDto($request, $route, $apiInterface, $methodName);
        $responseDto = $requestDto ? $service->{$methodName}($requestDto) : $service->{$methodName}();

        $response = new JsonResponse();
        $response->setEncodingOptions(JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

        if (is_object($responseDto)) {
            $response->setContent($this->serializer->createResponse($responseDto));
        }

        return $response;
    }
}
