<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator\Factory;

use OnMoon\OpenApiServerBundle\CodeGenerator\Naming\NamingStrategy;

abstract class BaseDefinitionFactory
{
    private string $rootNamespace;
    private string $rootPath;

    protected NamingStrategy $namingStrategy;

    public function __construct(
        NamingStrategy $namingStrategy,
        string $rootNamespace,
        string $rootPath
    ) {
        $this->namingStrategy = $namingStrategy;
        $this->rootNamespace  = $rootNamespace;
        $this->rootPath       = $rootPath;
    }

    public function rootNamespace() : string
    {
        return $this->rootNamespace;
    }

    public function rootPath() : string
    {
        return $this->rootPath;
    }
}
