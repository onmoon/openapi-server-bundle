<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Specification\Definitions;

class ObjectType
{
    /** @var Property[] $properties; */
    private array $properties;

    /**
     * @param Property[] $properties
     */
    public function __construct(array $properties)
    {
        $this->properties = $properties;
    }

    /**
     * @return Property[]
     */
    public function getProperties() : array
    {
        return $this->properties;
    }
}
