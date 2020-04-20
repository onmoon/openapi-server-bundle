<?php


namespace OnMoon\OpenApiServerBundle\CodeGenerator\Definitions;


class DtoDefinition extends GeneratedClassDefinition
{
    /**
     * @var PropertyDefinition[] $properties;
     */
    private array $properties;
    private ?ClassDefinition $implements = null;

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
     * @return PropertyDefinition[]
     */
    public function getProperties(): array
    {
        return $this->properties;
    }

    /**
     * @return ClassDefinition|null
     */
    public function getImplements(): ?ClassDefinition
    {
        return $this->implements;
    }

    /**
     * @param ClassDefinition|null $implements
     * @return DtoDefinition
     */
    public function setImplements(?ClassDefinition $implements): self
    {
        $this->implements = $implements;
        return $this;
    }

}
