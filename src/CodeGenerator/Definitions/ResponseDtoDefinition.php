<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator\Definitions;

class ResponseDtoDefinition extends DtoDefinition
{
    /** @var string|int */
    private $statusCode;

    /**
     * @param int|string           $statusCode
     * @param PropertyDefinition[] $properties
     */
    public function __construct($statusCode, array $properties)
    {
        $this->statusCode = $statusCode;
        parent::__construct($properties);
    }

    /**
     * @return int|string
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }
}
