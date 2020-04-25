<?php


namespace OnMoon\OpenApiServerBundle\Specification\Definitions;


class Specification
{
    /** @var Operation[] */
    private array $operations;

    /**
     * SpecificationDefinition constructor.
     * @param Operation[] $operations
     */
    public function __construct($operations)
    {
        $this->operations = $operations;
    }

    /**
     * @return Operation[]
     */
    public function getOperations(): array
    {
        return $this->operations;
    }
}