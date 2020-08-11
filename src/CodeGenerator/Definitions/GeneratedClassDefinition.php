<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator\Definitions;

class GeneratedClassDefinition extends ClassDefinition
{
    private string $fileName;
    private string $filePath;

    final public function getFileName(): string
    {
        return $this->fileName;
    }

    final public function setFileName(string $fileName): self
    {
        $this->fileName = $fileName;

        return $this;
    }

    final public function getFilePath(): string
    {
        return $this->filePath;
    }

    final public function setFilePath(string $filePath): self
    {
        $this->filePath = $filePath;

        return $this;
    }
}
