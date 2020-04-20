<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator\Definitions;

class ResponseDtoDefinition extends DtoDefinition
{
    private string $statusCode;

    /**
     * @param string|int $statusCode
     * @param array|PropertyDefinition[] $properties
     */
    public function __construct($statusCode, array $properties)
    {
        $this->statusCode = $statusCode.'';
        parent::__construct($properties);
    }

    public function getStatusCode() : string
    {
        return $this->statusCode;
    }
}
