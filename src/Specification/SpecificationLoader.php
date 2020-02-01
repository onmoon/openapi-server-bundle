<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Specification;

use cebe\openapi\Reader;
use cebe\openapi\spec\OpenApi;
use Exception;
use Symfony\Component\Config\FileLocatorInterface;
use function array_keys;
use function file_exists;
use function implode;
use function is_string;
use function pathinfo;
use function sprintf;
use function stream_is_local;
use const PATHINFO_EXTENSION;

class SpecificationLoader
{
    /**
     * @var Specification[]
     * @psalm-var array<string, Specification>
     */
    private array $specs = [];
    private FileLocatorInterface $locator;

    public function __construct(FileLocatorInterface $locator)
    {
        $this->locator = $locator;
    }

    /**
     * @param string[] $spec
     */
    public function registerSpec(string $name, array $spec) : void
    {
        $this->specs[$name] = new Specification(
            $spec['path'],
            $spec['type'] ?? null,
            $spec['name_space'],
            $spec['media_type']
        );
    }

    /**
     * @return Specification[]
     *
     * @psalm-return array<string, Specification>
     */
    public function list() : array
    {
        return $this->specs;
    }

    public function get(string $name) : Specification
    {
        if (empty($this->specs[$name])) {
            throw new Exception('OpenApi spec "' . $name . '" is not registered in bundle config, ' .
                'Registered specs are: ' . implode(', ', array_keys($this->specs)) . '.');
        }

        return $this->specs[$name];
    }

    public function load(string $name) : OpenApi
    {
        $spec     = $this->get($name);
        $specPath = $this->locator->locate($spec->getPath());

        if (! is_string($specPath)) {
            throw new Exception(sprintf('More than one file path found for specification "%s".', $spec->getPath()));
        }

        if (! stream_is_local($specPath)) {
            throw new Exception(sprintf('This is not a local file "%s".', $specPath));
        }

        if (! file_exists($specPath)) {
            throw new Exception(sprintf('File "%s" not found.', $specPath));
        }

        $type = $spec->getType();

        if ($type === null) {
            $type = pathinfo($specPath, PATHINFO_EXTENSION);
        }

        if ($type === 'yaml') {
            /**
             * phpcs:disable SlevomatCodingStandard.PHP.RequireExplicitAssertion.RequiredExplicitAssertion
             * @var OpenApi $specification
             */
            $specification = Reader::readFromYamlFile($specPath);

            return $specification;
        }

        if ($type === 'json') {
            /**
             * phpcs:disable SlevomatCodingStandard.PHP.RequireExplicitAssertion.RequiredExplicitAssertion
             * @var OpenApi $specification
             */
            $specification = Reader::readFromJsonFile($specPath);

            return $specification;
        }

        throw new Exception(
            sprintf(
                'Failed to determine spec type for "%s".
                Try specifying "type" parameter in bundle config with either "yaml" or "json" value',
                $specPath
            )
        );
    }
}
