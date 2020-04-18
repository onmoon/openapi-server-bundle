<?php


namespace OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions;


class DtoDefinition
{
    /**
     * @var PropertyDtoDefinition[] $properties;
     */
    private array $properties;
    private ?string $className = null;
    private ?string $fileName = null;
    private ?string $namespace = null;

    /**
     * DtoDefinition constructor.
     * @param array|PropertyDtoDefinition[] $properties
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
    public function getClassName(): ?string
    {
        return $this->className;
    }

    /**
     * @param string|null $className
     * @return DtoDefinition
     */
    public function setClassName(?string $className): DtoDefinition
    {
        $this->className = $className;
        return $this;
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
    public function setFileName(?string $fileName): DtoDefinition
    {
        $this->fileName = $fileName;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getNamespace(): ?string
    {
        return $this->namespace;
    }

    /**
     * @param string|null $namespace
     * @return DtoDefinition
     */
    public function setNamespace(?string $namespace): DtoDefinition
    {
        $this->namespace = $namespace;
        return $this;
    }

    /**
     * @return PropertyDefinition[]
     */
    public function getProperties(): array
    {
        return $this->properties;
    }

}
