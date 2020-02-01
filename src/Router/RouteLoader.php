<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Router;

use OnMoon\OpenApiServerBundle\Controller\ApiController;
use OnMoon\OpenApiServerBundle\OpenApi\ArgumentResolver;
use OnMoon\OpenApiServerBundle\Specification\SpecificationLoader;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class RouteLoader extends Loader implements LoaderInterface
{
    private SpecificationLoader $loader;
    private ArgumentResolver $argumentResolver;
    public const OPENAPI_TYPE      = 'open_api';
    public const OPENAPI_SPEC      = '_openapi_spec';
    public const OPENAPI_PATH      = '_openapi_path';
    public const OPENAPI_METHOD    = '_openapi_method';
    public const OPENAPI_ARGUMENTS = '_openapi_args';

    public function __construct(SpecificationLoader $loader, ArgumentResolver $argumentResolver)
    {
        $this->loader           = $loader;
        $this->argumentResolver = $argumentResolver;
    }

    /**
     * @inheritDoc
     */
    public function load($resource, $type = null)
    {
        $openApi = $this->loader->load($resource);

        $routes = new RouteCollection();

        foreach ($openApi->paths as $path => $pathItem) {
            foreach ($pathItem->getOperations() as $method => $operation) {
                [$types, $requirements] = $this->argumentResolver->resolveArgumentsTypeAndPattern(
                    $pathItem->parameters,
                    $operation->parameters
                );

                $defaults  = [
                    '_controller' => ApiController::class . '::handle',
                ];
                $options   = [
                    self::OPENAPI_SPEC => $resource,
                    self::OPENAPI_PATH => $path,
                    self::OPENAPI_METHOD => $method,
                    self::OPENAPI_ARGUMENTS => $types,
                ];
                $route     = new Route($path, $defaults, $requirements, $options, '', [], [$method]);
                $routeName = $operation->operationId;
                $routes->add($routeName, $route);
            }
        }

        return $routes;
    }

    /**
     * @inheritDoc
     */
    public function supports($resource, $type = null)
    {
        return $type === self::OPENAPI_TYPE;
    }
}
