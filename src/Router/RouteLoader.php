<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Router;

use cebe\openapi\Reader;
use OnMoon\OpenApiServerBundle\Controller\ApiController;
use OnMoon\OpenApiServerBundle\OpenApi\ArgumentResolver;
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use InvalidArgumentException;

class RouteLoader extends Loader implements LoaderInterface
{
    private FileLocatorInterface $locator;
    private ArgumentResolver $argumentResolver;
    const OPENAPI_JSON = 'openapi-json';
    const OPENAPI_YAML = 'openapi-yaml';
    const OPENAPI_SPEC = '_openapi_spec';
    const OPENAPI_SPEC_PATH = '_openapi_spec_path';
    const OPENAPI_PATH = '_openapi_path';
    const OPENAPI_METHOD = '_openapi_method';
    const OPENAPI_ARGUMENTS = '_openapi_args';

    /**
     * RouteLoader constructor.
     * @param FileLocatorInterface $locator
     * @param ArgumentResolver $argumentResolver
     */
    public function __construct(FileLocatorInterface $locator, ArgumentResolver $argumentResolver)
    {
        $this->locator = $locator;
        $this->argumentResolver = $argumentResolver;
    }

    /**
     * @inheritDoc
     */
    public function load($file, $type = null)
    {
        $specPath = $this->locator->locate($file);

        if (!stream_is_local($specPath)) {
            throw new InvalidArgumentException(sprintf('This is not a local file "%s".', $specPath));
        }

        if (!file_exists($specPath)) {
            throw new InvalidArgumentException(sprintf('File "%s" not found.', $specPath));
        }

        $openApi = null;
        if($type === self::OPENAPI_YAML) {
            $openApi = Reader::readFromYamlFile($specPath);
        } elseif($type === self::OPENAPI_JSON) {
            $openApi = Reader::readFromJsonFile($specPath);
        }

        if (is_null($openApi)) {
            throw new InvalidArgumentException(sprintf('File "%s" is could not be loaded.', $specPath));
        }

        $routes = new RouteCollection();

        foreach ($openApi->paths as $path => $pathItem) {
            foreach ($pathItem->getOperations() as $method => $operation) {
                list($types, $requirements) = $this->argumentResolver->resolveArgumentsTypeAndPattern(
                    $pathItem->parameters, $operation->parameters);

                $defaults = [
                    '_controller' => ApiController::class.'::handle',
                ];
                $options = [
                    self::OPENAPI_SPEC => $openApi,
                    self::OPENAPI_SPEC_PATH => $specPath,
                    self::OPENAPI_PATH => $path,
                    self::OPENAPI_METHOD => $method,
                    self::OPENAPI_ARGUMENTS => $types,
                ];
                $route = new Route($path, $defaults, $requirements, $options, '', [], [$method]);
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
        return (self::OPENAPI_JSON === $type) || (self::OPENAPI_YAML === $type);
    }
}
