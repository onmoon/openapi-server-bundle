<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions\Factory;

use cebe\openapi\spec\Operation;
use cebe\openapi\spec\Schema;
use Lukasoppermann\Httpstatus\Httpstatus;
use OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions\ResponseDtoDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Factory\OperationDefinitionFactory;
use OnMoon\OpenApiServerBundle\CodeGenerator\Naming\NamingStrategy;
use OnMoon\OpenApiServerBundle\Specification\Specification;
use Throwable;

class ResponseDtoDefinitionFactory extends OperationDefinitionFactory
{
    private Httpstatus $httpstatus;

    public function __construct(
        Specification $specification,
        Operation $operation,
        NamingStrategy $namingStrategy,
        Httpstatus $httpstatus,
        string $rootNamespace,
        string $rootPath
    ) {
        parent::__construct($specification, $operation, $namingStrategy, $rootNamespace, $rootPath);

        $this->httpstatus = $httpstatus;
    }

    public function create(Schema $schema, string $responseCode) : ResponseDtoDefinition
    {
        try {
            $statusNamespace = $this->httpstatus->getReasonPhrase((string) $responseCode);
        } catch (Throwable $e) {
            $statusNamespace = $responseCode;
        }

        $statusNamespace = $this->namingStrategy->stringToNamespace($statusNamespace);

        $responseDtoNamespace = $this->namingStrategy->buildNamespace(
            $this->operationNamespace(),
            self::DTO_NAMESPACE,
            self::RESPONSE_SUFFIX,
            $statusNamespace
        );
        $responseDtoClassName = $this->namingStrategy->stringToNamespace(
            $this->operationName() . $statusNamespace . self::DTO_SUFFIX
        );
        $responseDtoPath      = $this->namingStrategy->buildPath(
            $this->operationPath(),
            self::DTO_NAMESPACE,
            self::RESPONSE_SUFFIX,
            $statusNamespace
        );
        $responseDtoFileName  = $responseDtoClassName . '.php';

        $responseDtoDefinition = new ResponseDtoDefinition(
            $responseDtoPath,
            $responseDtoFileName,
            $responseDtoNamespace,
            $responseDtoClassName,
            $responseCode,
            $schema
        );

        $responseDtoDefinition->makeMutable();

        return $responseDtoDefinition;
    }
}
