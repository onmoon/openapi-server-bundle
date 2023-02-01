<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Specification\Definitions;

final class ObjectSchema implements GetSchema
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
    public function getProperties(): array
    {
        return $this->properties;
    }

    public function getSchema(): ObjectSchema
    {
        return $this;
    }
}
