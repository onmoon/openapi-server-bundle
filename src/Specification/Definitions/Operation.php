<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Specification\Definitions;

final class Operation
{
    /**
     * @param  array<string, ObjectSchema>                     $requestParameters
     * @param array<string|int, ObjectSchema|ObjectReference> $responses
     */
    public function __construct(
        private string $url,
        private string $method,
        private string $requestHandlerName,
        private ?string $summary = null,
        private ObjectSchema|ObjectReference|null $requestBody = null,
        private array $requestParameters = [],
        private array $responses = []
    ) {
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getRequestHandlerName(): string
    {
        return $this->requestHandlerName;
    }

    public function getSummary(): ?string
    {
        return $this->summary;
    }

    public function getRequestBody(): ObjectSchema|ObjectReference|null
    {
        return $this->requestBody;
    }

    /**
     * @return array<string, ObjectSchema>
     */
    public function getRequestParameters(): array
    {
        return $this->requestParameters;
    }

    /**
     * @return array<string|int, ObjectSchema|ObjectReference>
     */
    public function getResponses(): array
    {
        return $this->responses;
    }

    public function getResponse(string $code): ObjectSchema|ObjectReference
    {
        return $this->responses[$code];
    }
}
