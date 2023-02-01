<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator\Definitions;

use OnMoon\OpenApiServerBundle\Interfaces\Dto;

use function count;

class DtoDefinition extends GeneratedClassDefinition implements DtoReference
{
    /** @var PropertyDefinition[] $properties; */
    private array $properties;
    private ?ClassReference $implements;

    /**
     * @param PropertyDefinition[] $properties
     */
    public function __construct(array $properties)
    {
        $this->implements = ClassDefinition::fromFQCN(Dto::class);
        $this->properties = $properties;
    }

    final public function isEmpty(): bool
    {
        return count($this->properties) === 0;
    }

    /**
     * @return PropertyDefinition[]
     */
    final public function getProperties(): array
    {
        return $this->properties;
    }

    final public function getImplements(): ?ClassReference
    {
        return $this->implements;
    }

    final public function setImplements(?ClassReference $implements): self
    {
        $this->implements = $implements;

        return $this;
    }
}
