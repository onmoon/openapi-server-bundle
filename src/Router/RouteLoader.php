<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Router;

use OnMoon\OpenApiServerBundle\Controller\ApiController;
use OnMoon\OpenApiServerBundle\Specification\SpecificationLoader;
use OnMoon\OpenApiServerBundle\Types\ArgumentResolver;
use Stringable;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

use function array_key_exists;

final class RouteLoader extends Loader
{
    private SpecificationLoader $loader;
    private ArgumentResolver $argumentResolver;
    public const OPENAPI_TYPE      = 'open_api';
    public const OPENAPI_SPEC      = '_openapi_spec';
    public const OPENAPI_OPERATION = '_openapi_operation';

    public function __construct(SpecificationLoader $loader, ArgumentResolver $argumentResolver)
    {
        $this->loader           = $loader;
        $this->argumentResolver = $argumentResolver;
    }

    #[\Override]
    public function load(mixed $resource, ?string $type = null): RouteCollection
    {
        $specName      = (string) $resource;
        $specification = $this->loader->load($specName);

        $routes = new RouteCollection();

        foreach ($specification->getOperations() as $operationId => $operation) {
            /** @psalm-var array<(string|Stringable)> $requirements */
            $requirements = [];

            $parameters = $operation->getRequestParameters();
            if (array_key_exists('path', $parameters)) {
                $argumentPatterns = $this->argumentResolver->resolveArgumentPatterns($parameters['path']);
                foreach ($argumentPatterns as $name => $pattern) {
                    if (is_null($pattern)) {
                        continue;
                    }
                    $requirements[$name] = $pattern;
                }
            }

            $defaults  = [
                '_controller' => ApiController::class . '::handle',
            ];
            $options   = [
                self::OPENAPI_SPEC => $specName,
                self::OPENAPI_OPERATION => $operationId,
            ];
            $route     = new Route($operation->getUrl(), $defaults, $requirements, $options, '', [], [$operation->getMethod()]);
            $routeName = $operationId;
            $routes->add($routeName, $route);
        }

        return $routes;
    }

    /** @inheritDoc */
    #[\Override]
    public function supports($resource, ?string $type = null): bool
    {
        return $type === self::OPENAPI_TYPE;
    }
}
