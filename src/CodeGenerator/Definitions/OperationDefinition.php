<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator\Definitions;

final class OperationDefinition
{
    /**
     * @param ResponseDefinition[] $responses
     */
    public function __construct(
        private string $url,
        private string $method,
        private string $operationId,
        private string $requestHandlerName,
        private ?string $summary,
        private ?string $singleHttpCode,
        private ?DtoReference $request,
        private array $responses,
        private RequestHandlerInterfaceDefinition $requestHandlerInterface
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

    public function getRequest(): ?DtoReference
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
