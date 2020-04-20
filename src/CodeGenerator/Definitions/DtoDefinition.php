<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator\Definitions;

use function count;

class DtoDefinition extends GeneratedClassDefinition
{
    /** @var PropertyDefinition[] $properties; */
    private array $properties;
    private ?ClassDefinition $implements = null;

    /**
     * @param PropertyDefinition[] $properties
     */
    public function __construct(array $properties)
    {
        $this->properties = $properties;
    }

    public function isEmpty() : bool
    {
        return count($this->properties) === 0;
    }

    /**
     * @return PropertyDefinition[]
     */
    public function getProperties() : array
    {
        return $this->properties;
    }

    public function getImplements() : ?ClassDefinition
    {
        return $this->implements;
    }

    /**
     * @return DtoDefinition
     */
    public function setImplements(?ClassDefinition $implements) : self
    {
        $this->implements = $implements;

        return $this;
    }
}
