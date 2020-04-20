<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator\Definitions;

class GeneratedClassDefinition extends ClassDefinition
{
    private string $fileName;
    private string $filePath;

    public function getFileName() : string
    {
        return $this->fileName;
    }

    public function setFileName(string $fileName) : self
    {
        $this->fileName = $fileName;

        return $this;
    }

    public function getFilePath() : string
    {
        return $this->filePath;
    }

    public function setFilePath(string $filePath) : self
    {
        $this->filePath = $filePath;

        return $this;
    }
}
