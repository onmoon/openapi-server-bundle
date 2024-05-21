<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator\Definitions;

use OnMoon\OpenApiServerBundle\Specification\Definitions\SpecificationConfig;

final class SpecificationDefinition
{
    /**
     * @param OperationDefinition[] $operations
     * @param ComponentDefinition[] $components
     */
    public function __construct(private SpecificationConfig $specification, private array $operations, private array $components)
    {
    }

    /** @return OperationDefinition[] */
    public function getOperations(): array
    {
        return $this->operations;
    }

    public function getSpecification(): SpecificationConfig
    {
        return $this->specification;
    }

    /** @return ComponentDefinition[] */
    public function getComponents(): array
    {
        return $this->components;
    }
}
