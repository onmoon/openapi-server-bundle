<?php


namespace OnMoon\OpenApiServerBundle\CodeGenerator\Definitions;


class GeneratedClassDefinition extends ClassDefinition
{
    private ?string $fileName = null;
    private ?string $filePath = null;

    /**
     * @return string|null
     */
    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    /**
     * @param string|null $fileName
     * @return GeneratedClassDefinition
     */
    public function setFileName(?string $fileName): GeneratedClassDefinition
    {
        $this->fileName = $fileName;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getFilePath(): ?string
    {
        return $this->filePath;
    }

    /**
     * @param string|null $filePath
     * @return GeneratedClassDefinition
     */
    public function setFilePath(?string $filePath): GeneratedClassDefinition
    {
        $this->filePath = $filePath;
        return $this;
    }


}