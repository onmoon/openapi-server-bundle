<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions;

class ResponseDtoDefinition extends DtoDefinition
{
    private ?string $statusCode;

    /**
     * @return string|null
     */
    public function getStatusCode(): ?string
    {
        return $this->statusCode;
    }

    /**
     * @param string|null $statusCode
     * @return ResponseDtoDefinition
     */
    public function setStatusCode(?string $statusCode): ResponseDtoDefinition
    {
        $this->statusCode = $statusCode;
        return $this;
    }
}
