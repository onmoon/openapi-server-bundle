<?php


namespace OnMoon\OpenApiServerBundle\Specification\Definitions;


class OperationDefinition
{
    private string $url;
    private string $method;
    private ?string $summary                   = null;
    private ?ObjectDefinition $requestBody     = null;
    /** @var ObjectDefinition[] */
    private array $requestParameters = [];
    /** @var ObjectDefinition[] */
    private array $responses = [];

    /**
     * OperationDefinition constructor.
     * @param string $url
     * @param string $method
     * @param string|null $summary
     * @param ObjectDefinition|null $requestBody
     * @param array|ObjectDefinition[] $requestParameters
     * @param array|ObjectDefinition[] $responses
     */
    public function __construct(string $url, string $method, ?string $summary, ?ObjectDefinition $requestBody, $requestParameters, $responses)
    {
        $this->url = $url;
        $this->method = $method;
        $this->summary = $summary;
        $this->requestBody = $requestBody;
        $this->requestParameters = $requestParameters;
        $this->responses = $responses;
    }

}
