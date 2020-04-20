<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator\Definitions;

final class GeneratedFileDefinition
{
    private GeneratedClassDefinition $class;
    private string $fileContents;

    /**
     * GeneratedFileDefinition constructor.
     * @param GeneratedClassDefinition $class
     * @param string $fileContents
     */
    public function __construct(GeneratedClassDefinition $class, string $fileContents)
    {
        $this->class = $class;
        $this->fileContents = $fileContents;
    }

    /**
     * @return GeneratedClassDefinition
     */
    public function getClass(): GeneratedClassDefinition
    {
        return $this->class;
    }

    /**
     * @return string
     */
    public function getFileContents(): string
    {
        return $this->fileContents;
    }

    /**
     * @param string $fileContents
     * @return GeneratedFileDefinition
     */
    public function setFileContents(string $fileContents): GeneratedFileDefinition
    {
        $this->fileContents = $fileContents;
        return $this;
    }


}
