<?php


namespace OnMoon\OpenApiServerBundle\Specification\Definitions;


class Operation
{
    private string $url;
    private string $method;
    private ?string $summary                   = null;
    private ?ObjectType $requestBody     = null;
    /** @var ObjectType[] */
    private array $requestParameters = [];
    /** @var ObjectType[] */
    private array $responses = [];

    /**
     * OperationDefinition constructor.
     * @param string $url
     * @param string $method
     * @param string|null $summary
     * @param ObjectType|null $requestBody
     * @param array|ObjectType[] $requestParameters
     * @param array|ObjectType[] $responses
     */
    public function __construct(string $url, string $method, ?string $summary, ?ObjectType $requestBody, $requestParameters, $responses)
    {
        $this->url = $url;
        $this->method = $method;
        $this->summary = $summary;
        $this->requestBody = $requestBody;
        $this->requestParameters = $requestParameters;
        $this->responses = $responses;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @return string|null
     */
    public function getSummary(): ?string
    {
        return $this->summary;
    }

    /**
     * @return ObjectType|null
     */
    public function getRequestBody(): ?ObjectType
    {
        return $this->requestBody;
    }

    /**
     * @return ObjectType[]
     */
    public function getRequestParameters(): array
    {
        return $this->requestParameters;
    }

    /**
     * @return ObjectType[]
     */
    public function getResponses(): array
    {
        return $this->responses;
    }


}
