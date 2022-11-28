<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Specification\Definitions;

final class Operation
{
    private string $url;
    private string $method;
    private string $requestHandlerName;
    private ?string $summary;
    private ?ObjectSchema $requestBody;

    /** @var array<string, ObjectSchema> */
    private array $requestParameters;
    /** @var array<string|int, ObjectSchema> */
    private array $responses;

    /**
     * @param  array<string, ObjectSchema>     $requestParameters
     * @param array<string|int, ObjectSchema> $responses
     */
    public function __construct(
        string        $url,
        string        $method,
        string        $requestHandlerName,
        ?string       $summary = null,
        ?ObjectSchema $requestBody = null,
        array         $requestParameters = [],
        array         $responses = []
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

    public function getRequestBody(): ?ObjectSchema
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
     * @return array<string|int, ObjectSchema>
     */
    public function getResponses(): array
    {
        return $this->responses;
    }

    public function getResponse(string $code): ObjectSchema
    {
        return $this->responses[$code];
    }
}
