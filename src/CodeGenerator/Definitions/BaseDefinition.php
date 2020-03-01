<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator\Definitions;

abstract class BaseDefinition
{
    private string $directoryPath;
    private string $fileName;
    private string $namespace;
    private string $className;

    public function __construct(
        string $directoryPath,
        string $fileName,
        string $namespace,
        string $className
    ) {
        $this->directoryPath = $directoryPath;
        $this->fileName      = $fileName;
        $this->namespace     = $namespace;
        $this->className     = $className;
    }

    public function directoryPath() : string
    {
        return $this->directoryPath;
    }

    public function setDirectoryPath(string $directoryPath) : void
    {
        $this->directoryPath = $directoryPath;
    }

    public function fileName() : string
    {
        return $this->fileName;
    }

    public function setFileName(string $fileName) : void
    {
        $this->fileName = $fileName;
    }

    public function namespace() : string
    {
        return $this->namespace;
    }

    public function setNamespace(string $namesapce) : void
    {
        $this->namespace = $namesapce;
    }

    public function className() : string
    {
        return $this->className;
    }

    public function setClassName(string $className) : void
    {
        $this->className = $className;
    }
}
