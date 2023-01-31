<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator\Definitions;

use OnMoon\OpenApiServerBundle\Interfaces\RequestHandler;

final class RequestHandlerInterfaceDefinition extends GeneratedInterfaceDefinition
{
    private ?ClassDefinition $requestType;
    /** @var ClassDefinition[] */
    private array $responseTypes;
    private string $methodName;
    private ?string $methodDescription = null;

    /** @param ClassDefinition[] $responseTypes  */
    public function __construct(?ClassDefinition $requestType, array $responseTypes)
    {
        $this->requestType   = $requestType;
        $this->responseTypes = $responseTypes;
        $this->setExtends(ClassDefinition::fromFQCN(RequestHandler::class));
    }

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
