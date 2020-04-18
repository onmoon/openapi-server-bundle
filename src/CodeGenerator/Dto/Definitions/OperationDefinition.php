<?php


namespace OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions;


class OperationDefinition
{
    private string $url;
    private string $method;
    private string $operationId;
    private ?string $summary = null;
    private ?RequestDtoDefinition $request = null;
    private array $responses;

    /**
     * OperationDefinition constructor.
     * @param string $url
     * @param string $method
     * @param string $operationId
     * @param string|null $summary
     * @param RequestDtoDefinition|null $request
     * @param array $responses
     */
    public function __construct(string $url, string $method, string $operationId, ?string $summary, ?RequestDtoDefinition $request, array $responses)
    {
        $this->url = $url;
        $this->method = $method;
        $this->operationId = $operationId;
        $this->summary = $summary;
        $this->request = $request;
        $this->responses = $responses;
    }
}
