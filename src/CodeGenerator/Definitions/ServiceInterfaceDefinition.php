<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator\Definitions;

class ServiceInterfaceDefinition extends GeneratedInterfaceDefinition
{
    private ?ClassDefinition $requestType  = null;
    private ?ClassDefinition $responseType = null;
    private ?string $methodName            = null;
    private ?string $methodDescription     = null;

    public function getRequestType() : ?ClassDefinition
    {
        return $this->requestType;
    }

    public function setRequestType(?ClassDefinition $requestType) : ServiceInterfaceDefinition
    {
        $this->requestType = $requestType;

        return $this;
    }

    public function getResponseType() : ?ClassDefinition
    {
        return $this->responseType;
    }

    public function setResponseType(?ClassDefinition $responseType) : ServiceInterfaceDefinition
    {
        $this->responseType = $responseType;

        return $this;
    }

    public function getMethodName() : ?string
    {
        return $this->methodName;
    }

    public function setMethodName(?string $methodName) : ServiceInterfaceDefinition
    {
        $this->methodName = $methodName;

        return $this;
    }

    public function getMethodDescription() : ?string
    {
        return $this->methodDescription;
    }

    public function setMethodDescription(?string $methodDescription) : ServiceInterfaceDefinition
    {
        $this->methodDescription = $methodDescription;

        return $this;
    }
}
