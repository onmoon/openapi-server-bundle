<?php


namespace OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions;

class ServiceInterfaceDefinition extends GeneratedInterfaceDefinition
{
    private ?ClassDefinition $requestType = null;
    private ?ClassDefinition $responseType = null;
    private ?string $methodName = null;
    private ?string $methodDescription = null;

    /**
     * @return ClassDefinition|null
     */
    public function getRequestType(): ?ClassDefinition
    {
        return $this->requestType;
    }

    /**
     * @param ClassDefinition|null $requestType
     * @return ServiceInterfaceDefinition
     */
    public function setRequestType(?ClassDefinition $requestType): ServiceInterfaceDefinition
    {
        $this->requestType = $requestType;
        return $this;
    }

    /**
     * @return ClassDefinition|null
     */
    public function getResponseType(): ?ClassDefinition
    {
        return $this->responseType;
    }

    /**
     * @param ClassDefinition|null $responseType
     * @return ServiceInterfaceDefinition
     */
    public function setResponseType(?ClassDefinition $responseType): ServiceInterfaceDefinition
    {
        $this->responseType = $responseType;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getMethodName(): ?string
    {
        return $this->methodName;
    }

    /**
     * @param string|null $methodName
     * @return ServiceInterfaceDefinition
     */
    public function setMethodName(?string $methodName): ServiceInterfaceDefinition
    {
        $this->methodName = $methodName;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getMethodDescription(): ?string
    {
        return $this->methodDescription;
    }

    /**
     * @param string|null $methodDescription
     * @return ServiceInterfaceDefinition
     */
    public function setMethodDescription(?string $methodDescription): ServiceInterfaceDefinition
    {
        $this->methodDescription = $methodDescription;
        return $this;
    }



}