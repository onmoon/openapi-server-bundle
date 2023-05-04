<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator\Definitions;

final class ResponseDefinition
{
    private string $statusCode;
    private DtoReference $responseBody;

    public function __construct(string $statusCode, DtoReference $responseBody)
    {
        $this->statusCode   = $statusCode;
        $this->responseBody = $responseBody;
    }

    public function getStatusCode(): string
    {
        return $this->statusCode;
    }

    public function getResponseBody(): DtoReference
    {
        return $this->responseBody;
    }
}
