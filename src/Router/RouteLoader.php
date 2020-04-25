<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Router;

use OnMoon\OpenApiServerBundle\Controller\ApiController;
use OnMoon\OpenApiServerBundle\Specification\SpecificationLoader;
use OnMoon\OpenApiServerBundle\Types\ArgumentResolver;
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
    public const OPENAPI_OPERATION = '_openapi_operation';

    public function __construct(SpecificationLoader $loader, ArgumentResolver $argumentResolver)
    {
        $this->loader           = $loader;
        $this->argumentResolver = $argumentResolver;
    }

    /**
     * @inheritDoc
     */
    public function load($resource, $type = null) : RouteCollection
    {
        $specification = $this->loader->load((string) $resource);

        $routes = new RouteCollection();

        foreach ($specification->getOperations() as $operationId => $operation) {
            [$types, $requirements] = $this->argumentResolver->resolveArgumentsTypeAndPattern($operation->getRequestParameters());

            $defaults  = [
                '_controller' => ApiController::class . '::handle',
            ];
            $options   = [
                self::OPENAPI_SPEC => $resource,
                self::OPENAPI_PATH => $operation->getUrl(),
                self::OPENAPI_METHOD => $operation->getMethod(),
                self::OPENAPI_OPERATION => $operationId,
                self::OPENAPI_ARGUMENTS => $types,
            ];
            $route     = new Route($operation->getUrl(), $defaults, (array) $requirements, $options, '', [], [$operation->getMethod()]);
            $routeName = $operationId;
            $routes->add($routeName, $route);
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
