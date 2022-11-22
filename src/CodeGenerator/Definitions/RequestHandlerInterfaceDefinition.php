<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator\Definitions;

final class RequestHandlerInterfaceDefinition extends GeneratedInterfaceDefinition
{
    private ?ClassDefinition $requestType = null;
    /** @var ClassDefinition[] */
    private array $responseTypes = [];
    private string $methodName;
    private ?string $methodDescription = null;

    public function getRequestType(): ?ClassDefinition
    {
        return $this->requestType;
    }

    public function setRequestType(?ClassDefinition $requestType): self
    {
        $this->requestType = $requestType;

        return $this;
    }

    /** @return ClassDefinition[] */
    public function getResponseTypes(): array
    {
        return $this->responseTypes;
    }

    /** @param ClassDefinition[] $responseTypes */
    public function setResponseTypes(array $responseTypes): self
    {
        $this->responseTypes = $responseTypes;

        return $this;
    }

    public function getMethodName(): string
    {
        return $this->methodName;
    }

    public function setMethodName(string $methodName): self
    {
        $this->methodName = $methodName;

        return $this;
    }

    public function getMethodDescription(): ?string
    {
        return $this->methodDescription;
    }

    public function setMethodDescription(?string $methodDescription): self
    {
        $this->methodDescription = $methodDescription;

        return $this;
    }
}
