<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator\Factory;

use OnMoon\OpenApiServerBundle\CodeGenerator\Naming\NamingStrategy;
use OnMoon\OpenApiServerBundle\Specification\Specification;

abstract class SpecificationDefinitionFactory extends BaseDefinitionFactory
{
    public const APIS_NAMESPACE = 'Apis';

    private string $apiName;
    private string $mediaType;
    private string $namespace;
    private string $directoryPath;

    public function __construct(
        Specification $specification,
        NamingStrategy $namingStrategy,
        string $rootNamespace,
        string $rootPath
    ) {
        parent::__construct($namingStrategy, $rootNamespace, $rootPath);

        $this->apiName       = $this->namingStrategy->stringToNamespace($specification->getNameSpace());
        $this->mediaType     = $specification->getMediaType();
        $this->namespace     = $this->namingStrategy->buildNamespace($rootNamespace, self::APIS_NAMESPACE, $this->apiName);
        $this->directoryPath = $this->namingStrategy->buildPath($rootPath, self::APIS_NAMESPACE, $this->apiName);
    }

    public function apiName() : string
    {
        return $this->apiName;
    }

    public function apiMediaType() : string
    {
        return $this->mediaType;
    }

    public function apiNamespace() : string
    {
        return $this->namespace;
    }

    public function apiPath() : string
    {
        return $this->directoryPath;
    }
}
