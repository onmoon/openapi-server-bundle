<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions;

class ResponseDtoDefinition extends DtoDefinition
{
    private string $statusCode;

    /**
     * DtoDefinition constructor.
     * @param array|PropertyDtoDefinition[] $properties
     */
    public function __construct(string $statusCode, array $properties)
    {
        $this->statusCode = $statusCode;
        parent::__construct($properties);
    }


    public function getStatusCode(): string
    {
        return $this->statusCode;
    }
}
