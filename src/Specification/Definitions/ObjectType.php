<?php


namespace OnMoon\OpenApiServerBundle\Specification\Definitions;


class ObjectType
{
    /** @var Property[] $properties; */
    private array $properties;

    /**
     * ObjectDefinition constructor.
     * @param Property[] $properties
     */
    public function __construct($properties)
    {
        $this->properties = $properties;
    }

    /**
     * @return Property[]
     */
    public function getProperties(): array
    {
        return $this->properties;
    }
}
