<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator\Definitions;

class OperationDefinition
{
    private string $url;
    private string $method;
    private string $operationId;
    private string $serviceName;
    private ?string $summary                   = null;
    private ?RequestDtoDefinition $request     = null;
    private ?ClassDefinition $markersInterface = null;
    private ServiceInterfaceDefinition $serviceInterface;

    /** @var ResponseDtoDefinition[] */
    private array $responses;

    /**
     * @param ResponseDtoDefinition[] $responses
     */
    public function __construct(string $url, string $method, string $operationId, string $serviceName, ?string $summary, ?RequestDtoDefinition $request, array $responses)
    {
        $this->url         = $url;
        $this->method      = $method;
        $this->operationId = $operationId;
        $this->serviceName = $serviceName;
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

    public function getServiceName() : string
    {
        return $this->serviceName;
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

    public function getServiceInterface() : ServiceInterfaceDefinition
    {
        return $this->serviceInterface;
    }

    public function setServiceInterface(ServiceInterfaceDefinition $serviceInterface) : self
    {
        $this->serviceInterface = $serviceInterface;

        return $this;
    }
}
