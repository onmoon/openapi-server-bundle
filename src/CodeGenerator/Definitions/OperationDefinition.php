<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator\Definitions;

final class OperationDefinition
{
    private string $url;
    private string $method;
    private string $operationId;
    private string $requestHandlerName;
    private ?string $summary;
    private ?string $singleHttpCode;
    private ?DtoDefinition $request;

    /** @var ResponseDefinition[] */
    private array $responses;

    /**
     * @param ResponseDefinition[] $responses
     */
    public function __construct(
        string $url,
        string $method,
        string $operationId,
        string $requestHandlerName,
        ?string $summary,
        ?string $singleHttpCode,
        ?DtoDefinition $request,
        array $responses,
        private RequestHandlerInterfaceDefinition $requestHandlerInterface
    ) {
        $this->url                = $url;
        $this->method             = $method;
        $this->operationId        = $operationId;
        $this->requestHandlerName = $requestHandlerName;
        $this->summary            = $summary;
        $this->singleHttpCode     = $singleHttpCode;
        $this->request            = $request;
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

    public function getOperationId(): string
    {
        return $this->operationId;
    }

    public function getRequestHandlerName(): string
    {
        return $this->requestHandlerName;
    }

    public function getSummary(): ?string
    {
        return $this->summary;
    }

    public function getRequest(): ?DtoDefinition
    {
        return $this->request;
    }

    /**
     * @return ResponseDefinition[]
     */
    public function getResponses(): array
    {
        return $this->responses;
    }

    public function getRequestHandlerInterface(): RequestHandlerInterfaceDefinition
    {
        return $this->requestHandlerInterface;
    }

    public function getSingleHttpCode(): ?string
    {
        return $this->singleHttpCode;
    }
}
