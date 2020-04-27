<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator\Definitions;

class OperationDefinition
{
    private string $url;
    private string $method;
    private string $operationId;
    private string $requestHandlerName;
    private ?string $summary                   = null;
    private ?RequestDtoDefinition $request     = null;
    private ?ClassDefinition $markersInterface = null;
    private RequestHandlerInterfaceDefinition $requestHandlerInterface;

    /** @var ResponseDtoDefinition[] */
    private array $responses;

    /**
     * @param ResponseDtoDefinition[] $responses
     */
    public function __construct(string $url, string $method, string $operationId, string $requestHandlerName, ?string $summary, ?RequestDtoDefinition $request, array $responses)
    {
        $this->url                = $url;
        $this->method             = $method;
        $this->operationId        = $operationId;
        $this->requestHandlerName = $requestHandlerName;
        $this->summary            = $summary;
        $this->request            = $request;
        $this->responses          = $responses;
    }

    public function getUrl() : string
    {
        return $this->url;
    }

    public function getMethod() : string
    {
        return $this->method;
    }

    public function getOperationId() : string
    {
        return $this->operationId;
    }

    public function getRequestHandlerName() : string
    {
        return $this->requestHandlerName;
    }

    public function getSummary() : ?string
    {
        return $this->summary;
    }

    public function getRequest() : ?RequestDtoDefinition
    {
        return $this->request;
    }

    /**
     * @return ResponseDtoDefinition[]
     */
    public function getResponses() : array
    {
        return $this->responses;
    }

    public function getMarkersInterface() : ?ClassDefinition
    {
        return $this->markersInterface;
    }

    public function setMarkersInterface(?ClassDefinition $markersInterface) : self
    {
        $this->markersInterface = $markersInterface;

        return $this;
    }

    public function getRequestHandlerInterface() : RequestHandlerInterfaceDefinition
    {
        return $this->requestHandlerInterface;
    }

    public function setRequestHandlerInterface(RequestHandlerInterfaceDefinition $requestHandlerInterface) : self
    {
        $this->requestHandlerInterface = $requestHandlerInterface;

        return $this;
    }
}
