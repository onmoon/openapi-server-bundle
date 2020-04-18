<?php


namespace OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions;


class OperationDefinition
{
    private string $url;
    private string $method;
    private string $operationId;
    private ?string $summary = null;
    private ?RequestDtoDefinition $request = null;
    /**
     * @var ResponseDtoDefinition[]
     */
    private array $responses;

    /**
     * OperationDefinition constructor.
     * @param string $url
     * @param string $method
     * @param string $operationId
     * @param string|null $summary
     * @param RequestDtoDefinition|null $request
     * @param ResponseDtoDefinition[] $responses
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
     * @return string
     */
    public function getOperationId(): string
    {
        return $this->operationId;
    }

    /**
     * @return string|null
     */
    public function getSummary(): ?string
    {
        return $this->summary;
    }

    /**
     * @return RequestDtoDefinition|null
     */
    public function getRequest(): ?RequestDtoDefinition
    {
        return $this->request;
    }

    /**
     * @return ResponseDtoDefinition[]
     */
    public function getResponses(): array
    {
        return $this->responses;
    }


}
