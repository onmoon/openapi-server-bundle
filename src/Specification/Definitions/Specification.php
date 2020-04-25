<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Specification\Definitions;

use cebe\openapi\spec\OpenApi;

class Specification
{
    /** @var Operation[] */
    private array $operations;
    private OpenApi $openApi;

    /**
     * @param array|Operation[] $operations
     */
    public function __construct(array $operations, OpenApi $openApi)
    {
        $this->operations = $operations;
        $this->openApi    = $openApi;
    }

    /**
     * @return Operation[]
     */
    public function getOperations() : array
    {
        return $this->operations;
    }

    public function getOpenApi() : OpenApi
    {
        return $this->openApi;
    }
}
