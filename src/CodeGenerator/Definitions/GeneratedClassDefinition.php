<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator\Definitions;

class GeneratedClassDefinition extends ClassDefinition
{
    private ?string $fileName = null;
    private ?string $filePath = null;

    public function getFileName() : ?string
    {
        return $this->fileName;
    }

    public function setFileName(?string $fileName) : GeneratedClassDefinition
    {
        $this->fileName = $fileName;

        return $this;
    }

    public function getFilePath() : ?string
    {
        return $this->filePath;
    }

    public function setFilePath(?string $filePath) : GeneratedClassDefinition
    {
        $this->filePath = $filePath;

        return $this;
    }
}
