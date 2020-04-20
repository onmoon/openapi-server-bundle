<?php


namespace OnMoon\OpenApiServerBundle\CodeGenerator\Definitions;


class GeneratedInterfaceDefinition extends InterfaceDefinition
{
    private ?string $fileName = null;
    private ?string $filePath = null;
    private ?InterfaceDefinition $extends = null;

    /**
     * @return string|null
     */
    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    /**
     * @param string|null $fileName
     * @return GeneratedInterfaceDefinition
     */
    public function setFileName(?string $fileName): self
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
     * @return GeneratedInterfaceDefinition
     */
    public function setFilePath(?string $filePath): self
    {
        $this->filePath = $filePath;
        return $this;
    }

    /**
     * @return InterfaceDefinition|null
     */
    public function getExtends(): ?InterfaceDefinition
    {
        return $this->extends;
    }

    /**
     * @param InterfaceDefinition|null $extends
     * @return GeneratedInterfaceDefinition
     */
    public function setExtends(?InterfaceDefinition $extends): self
    {
        $this->extends = $extends;
        return $this;
    }

}