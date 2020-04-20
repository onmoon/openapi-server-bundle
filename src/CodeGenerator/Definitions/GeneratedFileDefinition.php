<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator\Definitions;

final class GeneratedFileDefinition
{
    private GeneratedClassDefinition $class;
    private string $fileContents;

    public function __construct(GeneratedClassDefinition $class, string $fileContents)
    {
        $this->class        = $class;
        $this->fileContents = $fileContents;
    }

    public function getClass() : GeneratedClassDefinition
    {
        return $this->class;
    }

    public function getFileContents() : string
    {
        return $this->fileContents;
    }

    public function setFileContents(string $fileContents) : GeneratedFileDefinition
    {
        $this->fileContents = $fileContents;

        return $this;
    }
}
