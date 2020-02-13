<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator\RequestHandlerInterface\Definitions;

use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\BaseDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions\RequestDtoDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions\ResponseDtoDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions\ResponseDtoMarkerInterfaceDefinition;

class RequestHandlerInterfaceDefinition extends BaseDefinition
{
    private string $methodName;
    private ?string $summary;
    private ?RequestDtoDefinition $requestDtoDefinition;
    /**
     * @var ResponseDtoDefinition[]
     * @psalm-var list<ResponseDtoDefinition>
     */
    private array $responseDtoDefinitions;
    private ?ResponseDtoMarkerInterfaceDefinition $responseDtoMarkerInterfaceDefinition;

    public function __construct(
        string $directoryPath,
        string $fileName,
        string $namespace,
        string $className,
        string $methodName
    ) {
        parent::__construct($directoryPath, $fileName, $namespace, $className);

        $this->methodName                           = $methodName;
        $this->summary                              = null;
        $this->requestDtoDefinition                 = null;
        $this->responseDtoDefinitions               = [];
        $this->responseDtoMarkerInterfaceDefinition = null;
    }

    public function methodName() : string
    {
        return $this->methodName;
    }

    public function setMethodName(string $methodName) : void
    {
        $this->methodName = $methodName;
    }

    public function summary() : ?string
    {
        return $this->summary;
    }

    public function setSummary(?string $summary) : void
    {
        $this->summary = $summary;
    }

    public function requestDtoDefinition() : ?RequestDtoDefinition
    {
        return $this->requestDtoDefinition;
    }

    public function setRequestDtoDefinition(?RequestDtoDefinition $definition) : void
    {
        $this->requestDtoDefinition = $definition;
    }

    /**
     * @return ResponseDtoDefinition[]
     *
     * @psalm-return list<ResponseDtoDefinition>
     */
    public function responseDtoDefinitions() : array
    {
        return $this->responseDtoDefinitions;
    }

    public function setResponseDtoDefinitions(ResponseDtoDefinition ...$definitions) : void
    {
        $this->responseDtoDefinitions = $definitions;
    }

    public function responseDtoMarkerInterfaceDefinition() : ?ResponseDtoMarkerInterfaceDefinition
    {
        return $this->responseDtoMarkerInterfaceDefinition;
    }

    public function setResponseDtoMarkerInterfaceDefinition(?ResponseDtoMarkerInterfaceDefinition $definition) : void
    {
        $this->responseDtoMarkerInterfaceDefinition = $definition;
    }
}
