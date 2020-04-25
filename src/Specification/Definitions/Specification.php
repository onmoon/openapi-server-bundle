<?php


namespace OnMoon\OpenApiServerBundle\Specification\Definitions;


use cebe\openapi\spec\OpenApi;

class Specification
{
    /** @var Operation[] */
    private array $operations;
    private OpenApi $openApi;

    /**
     * Specification constructor.
     * @param array|Operation[] $operations
     * @param OpenApi $openApi
     */
    public function __construct($operations, OpenApi $openApi)
    {
        $this->operations = $operations;
        $this->openApi = $openApi;
    }

    /**
     * @return Operation[]
     */
    public function getOperations(): array
    {
        return $this->operations;
    }

    /**
     * @return OpenApi
     */
    public function getOpenApi(): OpenApi
    {
        return $this->openApi;
    }


}