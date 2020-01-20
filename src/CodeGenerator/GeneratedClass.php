<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator;

final class GeneratedClass
{
    private string $fileDirectoryPath;
    private string $fileName;
    private string $namespace;
    private string $className;
    private string $fileContents;

    public function __construct(
        string $fileDirectoryPath,
        string $fileName,
        string $namespace,
        string $className,
        string $fileContents
    ) {
        $this->fileDirectoryPath = $fileDirectoryPath;
        $this->fileName          = $fileName;
        $this->namespace         = $namespace;
        $this->className         = $className;
        $this->fileContents      = $fileContents;
    }

    public function getFileDirectoryPath() : string
    {
        return $this->fileDirectoryPath;
    }

    public function getFileName() : string
    {
        return $this->fileName;
    }

    public function getNamespace() : string
    {
        return $this->namespace;
    }

    public function getClassName() : string
    {
        return $this->className;
    }

    public function getFQCN() : string
    {
        return $this->getNamespace() . '\\' . $this->getClassName();
    }

    public function getFileContents() : string
    {
        return $this->fileContents;
    }
}
