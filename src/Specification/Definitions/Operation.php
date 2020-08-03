<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Specification\Definitions;

class Operation
{
    private string $url;
    private string $method;
    private string $requestHandlerName;
    private ?string $summary;
    private ?ObjectType $requestBody;

    /**
     * @var ObjectType[]
     * @psalm-var array<string, ObjectType>
     */
    private array $requestParameters;
    /**
     * @var ObjectType[]
     * @psalm-var array<string|int, ObjectType>
     */
    private array $responses;

    /**
     * @param ObjectType[] $requestParameters
     * @param ObjectType[] $responses
     *
     * @psalm-param array<string, ObjectType> $requestParameters
     * @psalm-param array<string|int, ObjectType> $responses
     */
    public function __construct(
        string $url,
        string $method,
        string $requestHandlerName,
        ?string $summary = null,
        ?ObjectType $requestBody = null,
        array $requestParameters = [],
        array $responses = []
    ) {
        $this->url                = $url;
        $this->method             = $method;
        $this->requestHandlerName = $requestHandlerName;
        $this->summary            = $summary;
        $this->requestBody        = $requestBody;
        $this->requestParameters  = $requestParameters;
        $this->responses          = $responses;
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

    public function getRequestBody(): ?ObjectType
    {
        return $this->requestBody;
    }

    /**
     * @return ObjectType[]
     *
     * @psalm-return array<string, ObjectType>
     */
    public function getRequestParameters(): array
    {
        return $this->requestParameters;
    }

    /**
     * @return ObjectType[]
     *
     * @psalm-return array<string|int, ObjectType>
     */
    public function getResponses(): array
    {
        return $this->responses;
    }

    public function getResponse(string $code): ObjectType
    {
        return $this->responses[$code];
    }
}
