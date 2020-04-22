<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Router;

use cebe\openapi\spec\Parameter;
use cebe\openapi\spec\Reference;
use OnMoon\OpenApiServerBundle\Controller\ApiController;
use OnMoon\OpenApiServerBundle\Specification\SpecificationLoader;
use OnMoon\OpenApiServerBundle\Types\ArgumentResolver;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use function array_filter;

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
    public function load($resource, $type = null) : RouteCollection
    {
        $openApi = $this->loader->load((string) $resource);

        $routes = new RouteCollection();

        /**
         * phpcs:disable SlevomatCodingStandard.PHP.RequireExplicitAssertion.RequiredExplicitAssertion
         * @var string $path
         */
        foreach ($openApi->paths as $path => $pathItem) {
            /**
             * phpcs:disable SlevomatCodingStandard.PHP.RequireExplicitAssertion.RequiredExplicitAssertion
             * @var string $method
             */
            foreach ($pathItem->getOperations() as $method => $operation) {
                [$types, $requirements] = $this->argumentResolver->resolveArgumentsTypeAndPattern(
                    $this->filterParameters($pathItem->parameters),
                    $this->filterParameters($operation->parameters),
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
                $route     = new Route($path, $defaults, (array) $requirements, $options, '', [], [$method]);
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

    /**
     * @param Parameter[]|Reference[] $parametersOrReferences
     *
     * @return Parameter[]
     */
    private function filterParameters(array $parametersOrReferences) : array
    {
        /** @var Parameter[] $parameters */
        $parameters = array_filter(
            $parametersOrReferences,
            static fn($parameters) : bool => $parameters instanceof Parameter
        );

        return $parameters;
    }
}
