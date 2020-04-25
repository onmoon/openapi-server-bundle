<?php


namespace OnMoon\OpenApiServerBundle\Specification\Definitions;


class ObjectDefinition
{
    /** @var PropertyDefinition[] $properties; */
    private array $properties;

    /**
     * ObjectDefinition constructor.
     * @param PropertyDefinition[] $properties
     */
    public function __construct($properties)
    {
        $this->properties = $properties;
    }

    /**
     * @return PropertyDefinition[]
     */
    public function getProperties(): array
    {
        return $this->properties;
    }
}
