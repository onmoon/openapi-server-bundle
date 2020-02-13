<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions\Factory;

use cebe\openapi\spec\Parameter;
use OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions\RequestBodyDtoDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions\RequestDtoDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Factory\OperationDefinitionFactory;
use function array_filter;

class RequestDtoDefinitionFactory extends OperationDefinitionFactory
{
    public function create(
        ?RequestBodyDtoDefinition $requestBodyDtoDefinition,
        Parameter ...$parameters
    ) : RequestDtoDefinition {
        $requestDtoNamespace = $this->namingStrategy->buildNamespace(
            $this->operationNamespace(),
            self::DTO_NAMESPACE,
            self::REQUEST_SUFFIX
        );
        $requestDtoClassName = $this->namingStrategy->stringToNamespace(
            $this->operationName() . self::REQUEST_SUFFIX . self::DTO_SUFFIX
        );
        $requestDtoPath      = $this->namingStrategy->buildPath(
            $this->operationPath(),
            self::DTO_NAMESPACE,
            self::REQUEST_SUFFIX
        );
        $requestDtoFileName  = $requestDtoClassName . '.php';

        $requestDtoDefinition = new RequestDtoDefinition(
            $requestDtoPath,
            $requestDtoFileName,
            $requestDtoNamespace,
            $requestDtoClassName
        );
        $requestDtoDefinition->setRequestBodyDtoDefinition($requestBodyDtoDefinition);
        $requestDtoDefinition->setPathParameters(...$this->filterSupportedParameters('path', ...$parameters));
        $requestDtoDefinition->setQueryParameters(...$this->filterSupportedParameters('query', ...$parameters));

        return $requestDtoDefinition;
    }

    /**
     * @return Parameter[]
     */
    private function filterSupportedParameters(string $in, Parameter ...$parameters) : array
    {
        return array_filter($parameters, static fn ($parameter) : bool => $parameter->in === $in);
    }
}
