<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions\Factory;

use cebe\openapi\spec\Schema;
use OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions\RequestBodyDtoDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Factory\OperationDefinitionFactory;

class RequestBodyDtoDefinitionFactory extends OperationDefinitionFactory
{
    private const REQUEST_BODY_SUFFIX = 'RequestBody';

    public function create(Schema $schema) : RequestBodyDtoDefinition
    {
        $requestBodyDtoNamespace = $this->namingStrategy->buildNamespace(
            $this->operationNamespace(),
            self::DTO_NAMESPACE,
            self::REQUEST_SUFFIX
        );
        $requestBodyDtoClassName = $this->namingStrategy->stringToNamespace(
            $this->operationName() . self::REQUEST_BODY_SUFFIX . self::DTO_SUFFIX
        );
        $requestBodyDtoPath      = $this->namingStrategy->buildPath(
            $this->operationPath(),
            self::DTO_NAMESPACE,
            self::REQUEST_SUFFIX
        );
        $requestBodyDtoFileName  = $requestBodyDtoClassName . '.php';

        $requestBodyDtoDefinition = new RequestBodyDtoDefinition(
            $requestBodyDtoPath,
            $requestBodyDtoFileName,
            $requestBodyDtoNamespace,
            $requestBodyDtoClassName,
            $schema
        );
        $requestBodyDtoDefinition->makeImmutable();

        return $requestBodyDtoDefinition;
    }
}
