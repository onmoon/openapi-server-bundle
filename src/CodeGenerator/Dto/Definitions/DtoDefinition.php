<?php


namespace OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions;


class DtoDefinition
{
    /**
     * @var PropertyDtoDefinition[] $properties;
     */
    private array $properties;

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
}
