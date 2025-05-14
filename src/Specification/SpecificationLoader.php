<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Specification;

use cebe\openapi\Reader;
use cebe\openapi\spec\OpenApi;
use Exception;
use OnMoon\OpenApiServerBundle\Specification\Definitions\Specification;
use OnMoon\OpenApiServerBundle\Specification\Definitions\SpecificationConfig;
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

use function array_keys;
use function file_exists;
use function implode;
use function pathinfo;
use function stream_is_local;

use const PATHINFO_EXTENSION;

class SpecificationLoader
{
    public const CACHE_TAG         = 'openapi.server.bundle.specifications';
    private const CACHE_KEY_PREFIX = 'openapi-server-bundle-specification-';

    /**
     * @var SpecificationConfig[]
     * @psalm-var array<string, SpecificationConfig>
     */
    private array $specs = [];
    private SpecificationParser $parser;
    private FileLocatorInterface $locator;
    private TagAwareCacheInterface $cache;

    public function __construct(SpecificationParser $parser, FileLocatorInterface $locator, TagAwareCacheInterface $cache)
    {
        $this->parser  = $parser;
        $this->locator = $locator;
        $this->cache   = $cache;
    }

    /** @param array{path:string,type:string|null,name_space:string,media_type:string,date_time_class:string|null} $spec */
    public function registerSpec(string $name, array $spec): void
    {
        $this->specs[$name] = new SpecificationConfig(
            $spec['path'],
            $spec['type'] ?? null,
            $spec['name_space'],
            $spec['media_type'],
            $spec['date_time_class'] ?? null
        );
    }

    /**
     * @return SpecificationConfig[]
     * @psalm-return array<string, SpecificationConfig>
     */
    public function list(): array
    {
        return $this->specs;
    }

    public function get(string $name): SpecificationConfig
    {
        if (! isset($this->specs[$name])) {
            throw new Exception('OpenApi spec "' . $name . '" is not registered in bundle config, ' .
                'Registered specs are: ' . implode(', ', array_keys($this->specs)) . '.');
        }

        return $this->specs[$name];
    }

    public function load(string $name): Specification
    {
        return $this->cache->get(
            self::CACHE_KEY_PREFIX . $name,
            function (ItemInterface $cacheItem) use ($name): Specification {
                $cacheItem->tag(self::CACHE_TAG);

                return $this->parseSpecification($name, $this->get($name));
            }
        );
    }

    private function parseSpecification(string $specificationName, SpecificationConfig $specificationConfig): Specification
    {
        $specPath = $this->locator->locate($specificationConfig->getPath());

        if (! stream_is_local($specPath)) {
            throw new Exception(sprintf('This is not a local file "%s".', $specPath));
        }

        if (! file_exists($specPath)) {
            throw new Exception(sprintf('File "%s" not found.', $specPath));
        }

        $type = $specificationConfig->getType();

        if ($type === null) {
            $type = pathinfo($specPath, PATHINFO_EXTENSION);
        }

        $specification = null;
        if ($type === 'yaml') {
            $specification = Reader::readFromYamlFile($specPath, OpenApi::class, true);
        }

        if ($type === 'json') {
            $specification = Reader::readFromJsonFile($specPath);
        }

        if (! ($specification instanceof OpenApi)) {
            throw new Exception(
                sprintf(
                    'Failed to determine spec type for "%s".
                    Try specifying "type" parameter in bundle config with either "yaml" or "json" value',
                    $specPath
                )
            );
        }

        return $this->parser->parseOpenApi($specificationName, $specificationConfig, $specification);
    }
}
