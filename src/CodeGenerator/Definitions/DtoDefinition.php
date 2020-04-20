<?php


namespace OnMoon\OpenApiServerBundle\CodeGenerator\Definitions;


class DtoDefinition extends ClassDefinition
{
    /**
     * @var PropertyDefinition[] $properties;
     */
    private array $properties;
    private ?string $fileName = null;
    private ?string $filePath = null;
    private ?InterfaceDefinition $implements = null;

    /**
     * DtoDefinition constructor.
     * @param PropertyDefinition[] $properties
     */
    public function __construct($properties)
    {
        $this->properties = $properties;
    }

    public function isEmpty(): bool {
        return (count($this->properties) === 0);
    }

    /**
     * @return string|null
     */
    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    /**
     * @param string|null $fileName
     * @return DtoDefinition
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
     * @return DtoDefinition
     */
    public function setFilePath(?string $filePath): self
    {
        $this->filePath = $filePath;
        return $this;
    }

    /**
     * @return PropertyDefinition[]
     */
    public function getProperties(): array
    {
        return $this->properties;
    }

    /**
     * @return InterfaceDefinition|null
     */
    public function getImplements(): ?InterfaceDefinition
    {
        return $this->implements;
    }

    /**
     * @param InterfaceDefinition|null $implements
     * @return DtoDefinition
     */
    public function setImplements(?InterfaceDefinition $implements): self
    {
        $this->implements = $implements;
        return $this;
    }

}
