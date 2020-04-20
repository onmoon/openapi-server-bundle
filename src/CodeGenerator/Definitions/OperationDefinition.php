<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator\Definitions;

class OperationDefinition
{
    private string $url;
    private string $method;
    private string $operationId;
    private ?string $summary                              = null;
    private ?RequestDtoDefinition $request                = null;
    private ?ClassDefinition $markersInterface            = null;
    private ?ServiceInterfaceDefinition $serviceInterface = null;

    /** @var ResponseDtoDefinition[] */
    private array $responses;

    /**
     * @param ResponseDtoDefinition[] $responses
     */
    public function __construct(string $url, string $method, string $operationId, ?string $summary, ?RequestDtoDefinition $request, array $responses)
    {
        $this->url         = $url;
        $this->method      = $method;
        $this->operationId = $operationId;
        $this->summary     = $summary;
        $this->request     = $request;
        $this->responses   = $responses;
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

    public function setMarkersInterface(?ClassDefinition $markersInterface) : OperationDefinition
    {
        $this->markersInterface = $markersInterface;

        return $this;
    }

    public function getServiceInterface() : ?ServiceInterfaceDefinition
    {
        return $this->serviceInterface;
    }

    public function setServiceInterface(?ServiceInterfaceDefinition $serviceInterface) : OperationDefinition
    {
        $this->serviceInterface = $serviceInterface;

        return $this;
    }
}
