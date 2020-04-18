<?php


namespace OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions;


use OnMoon\OpenApiServerBundle\Specification\Specification;

class SpecificationDefinition
{
    private Specification $specification;
    /**
     * @var OperationDefinition[]
     */
    private array $operations;

    /**
     * SpecificationDefinition constructor.
     * @param Specification $specification
     * @param array|OperationDefinition[] $operations
     */
    public function __construct(Specification $specification, $operations)
    {
        $this->specification = $specification;
        $this->operations = $operations;
    }

    /**
     * @return OperationDefinition[]
     */
    public function getOperations(): array
    {
        return $this->operations;
    }

    /**
     * @return Specification
     */
    public function getSpecification(): Specification
    {
        return $this->specification;
    }



}