<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator\Definitions;

class ResponseDtoDefinition extends DtoDefinition
{
    private string $statusCode;

    /**
     * @param PropertyDefinition[] $properties
     */
    public function __construct(string $statusCode, array $properties)
    {
        $this->statusCode = $statusCode;
        parent::__construct($properties);
    }

    public function getStatusCode() : string
    {
        return $this->statusCode;
    }
}
