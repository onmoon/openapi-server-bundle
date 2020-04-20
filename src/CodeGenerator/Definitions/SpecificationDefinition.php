<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator\Definitions;

use OnMoon\OpenApiServerBundle\Specification\Specification;

class SpecificationDefinition
{
    private Specification $specification;
    /** @var OperationDefinition[] */
    private array $operations;

    /**
     * @param array|OperationDefinition[] $operations
     */
    public function __construct(Specification $specification, array $operations)
    {
        $this->specification = $specification;
        $this->operations    = $operations;
    }

    /**
     * @return OperationDefinition[]
     */
    public function getOperations() : array
    {
        return $this->operations;
    }

    public function getSpecification() : Specification
    {
        return $this->specification;
    }
}
