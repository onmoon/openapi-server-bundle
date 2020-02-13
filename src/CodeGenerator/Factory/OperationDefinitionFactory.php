<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator\Factory;

use cebe\openapi\spec\Operation;
use OnMoon\OpenApiServerBundle\CodeGenerator\Naming\NamingStrategy;
use OnMoon\OpenApiServerBundle\Specification\Specification;

abstract class OperationDefinitionFactory extends SpecificationDefinitionFactory
{
    protected const DTO_NAMESPACE   = 'Dto';
    protected const REQUEST_SUFFIX  = 'Request';
    protected const RESPONSE_SUFFIX = 'Response';
    protected const DTO_SUFFIX      = 'Dto';

    private string $operationId;
    private string $operationName;
    private string $operationNamespace;
    private string $operationPath;

    public function __construct(
        Specification $specification,
        Operation $operation,
        NamingStrategy $namingStrategy,
        string $rootNamespace,
        string $rootPath
    ) {
        parent::__construct($specification, $namingStrategy, $rootNamespace, $rootPath);

        $this->operationId        = $operation->operationId;
        $this->operationName      = $this->namingStrategy->stringToNamespace($operation->operationId);
        $this->operationNamespace = $this->namingStrategy->buildNamespace($this->apiNamespace(), $this->operationName);
        $this->operationPath      = $this->namingStrategy->buildPath($this->apiPath(), $this->operationName);
    }

    public function operationId() : string
    {
        return $this->operationId;
    }

    public function operationName() : string
    {
        return $this->operationName;
    }

    public function operationNamespace() : string
    {
        return $this->operationNamespace;
    }

    public function operationPath() : string
    {
        return $this->operationPath;
    }
}
