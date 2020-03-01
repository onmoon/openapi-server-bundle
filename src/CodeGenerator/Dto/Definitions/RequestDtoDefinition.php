<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions;

use cebe\openapi\spec\Parameter;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\BaseDefinition;

class RequestDtoDefinition extends BaseDefinition
{
    private ?RequestBodyDtoDefinition $requestBodyDtoDefinition;
    /**
     * @var Parameter[] $pathParameters
     * @psalm-var list<Parameter> $pathParameters
     */
    private array $pathParameters = [];
    /**
     * @var Parameter[] $queryParameters
     * @psalm-var list<Parameter> $queryParameters
     */
    private array $queryParameters = [];

    public function __construct(
        string $directoryPath,
        string $fileName,
        string $namespace,
        string $className
    ) {
        parent::__construct($directoryPath, $fileName, $namespace, $className);

        $this->requestBodyDtoDefinition = null;
        $this->pathParameters           = [];
        $this->queryParameters          = [];
    }

    public function requestBodyDtoDefiniton() : ?RequestBodyDtoDefinition
    {
        return $this->requestBodyDtoDefinition;
    }

    public function setRequestBodyDtoDefinition(?RequestBodyDtoDefinition $definition) : void
    {
        $this->requestBodyDtoDefinition = $definition;
    }

    /**
     * @return Parameter[]
     *
     * @psalm-return list<Parameter>
     */
    public function pathParameters() : array
    {
        return $this->pathParameters;
    }

    public function setPathParameters(Parameter ...$pathParameters) : void
    {
        $this->pathParameters = $pathParameters;
    }

    /**
     * @return Parameter[]
     *
     * @psalm-return list<Parameter>
     */
    public function queryParameters() : array
    {
        return $this->queryParameters;
    }

    public function setQueryParameters(Parameter ...$queryParameters) : void
    {
        $this->queryParameters = $queryParameters;
    }
}
