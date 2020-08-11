<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator\Definitions;

use OnMoon\OpenApiServerBundle\Specification\Definitions\SpecificationConfig;

final class SpecificationDefinition
{
    private SpecificationConfig $specification;
    /** @var OperationDefinition[] */
    private array $operations;

    /**
     * @param OperationDefinition[] $operations
     */
    public function __construct(SpecificationConfig $specification, array $operations)
    {
        $this->specification = $specification;
        $this->operations    = $operations;
    }

    /**
     * @return OperationDefinition[]
     */
    public function getOperations(): array
    {
        return $this->operations;
    }

    public function getSpecification(): SpecificationConfig
    {
        return $this->specification;
    }
}
