<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator\Definitions;

use OnMoon\OpenApiServerBundle\Interfaces\RequestHandler;

final class RequestHandlerInterfaceDefinition extends GeneratedInterfaceDefinition
{
    private ?DtoReference $requestType;
    /** @var DtoReference[] */
    private array $responseTypes;
    private string $methodName;
    private ?string $methodDescription = null;

    /** @param DtoReference[] $responseTypes  */
    public function __construct(?DtoReference $requestType, array $responseTypes)
    {
        $this->requestType   = $requestType;
        $this->responseTypes = $responseTypes;
        $this->setExtends(ClassDefinition::fromFQCN(RequestHandler::class));
    }

    public function getRequestType(): ?DtoReference
    {
        return $this->requestType;
    }

    public function setRequestType(?DtoReference $requestType): self
    {
        $this->requestType = $requestType;

        return $this;
    }

    /** @return DtoReference[] */
    public function getResponseTypes(): array
    {
        return $this->responseTypes;
    }

    /** @param DtoReference[] $responseTypes */
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
