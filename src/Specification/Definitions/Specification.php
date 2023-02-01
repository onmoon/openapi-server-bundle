<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Specification\Definitions;

use cebe\openapi\spec\OpenApi;

final class Specification
{
    /**
     * @param array<string, Operation> $operations
     * @param array<string, ObjectSchema> $componentSchemas
     */
    public function __construct(
        private array   $operations,
        private array   $componentSchemas,
        private OpenApi $openApi
    ) {

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

    /**
     * @return array<string, ObjectSchema>
     */
    public function getComponentSchemas(): array
    {
        return $this->componentSchemas;
    }

    public function getOpenApi(): OpenApi
    {
        return $this->openApi;
    }
}
