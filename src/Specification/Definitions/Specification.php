<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Specification\Definitions;

use cebe\openapi\spec\OpenApi;

final class Specification
{
    /**
     * @var Operation[]
     * @psalm-var array<string, Operation>
     */
    private array $operations;
    private OpenApi $openApi;

    /**
     * @param Operation[] $operations
     * @psalm-param array<string, Operation> $operations
     */
    public function __construct(array $operations, OpenApi $openApi)
    {
        $this->operations = $operations;
        $this->openApi    = $openApi;
    }

    /**
     * @return Operation[]
     * @psalm-return array<string, Operation>
     */
    public function getOperations(): array
    {
        return $this->operations;
    }

    public function getOperation(string $id): Operation
    {
        return $this->operations[$id];
    }

    public function getOpenApi(): OpenApi
    {
        return $this->openApi;
    }
}
