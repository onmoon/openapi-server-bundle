<?php


namespace OnMoon\OpenApiServerBundle\Specification\Definitions;


class SpecificationDefinition
{
    /** @var OperationDefinition[] */
    private array $operations;

    /**
     * SpecificationDefinition constructor.
     * @param OperationDefinition[] $operations
     */
    public function __construct($operations)
    {
        $this->operations = $operations;
    }

    /**
     * @return OperationDefinition[]
     */
    public function getOperations(): array
    {
        return $this->operations;
    }
}