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

}
