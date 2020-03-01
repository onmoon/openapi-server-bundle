<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator\RequestHandlerInterface\Definitions\Factory;

use OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions\RequestDtoDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions\ResponseDtoDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions\ResponseDtoMarkerInterfaceDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Factory\OperationDefinitionFactory;
use OnMoon\OpenApiServerBundle\CodeGenerator\RequestHandlerInterface\Definitions\RequestHandlerInterfaceDefinition;

class RequestHandlerInterfaceDefinitionFactory extends OperationDefinitionFactory
{
    public function create(
        ?RequestDtoDefinition $requestDtoDefinition,
        ?string $summary,
        ?ResponseDtoMarkerInterfaceDefinition $responseDtoMarkerInterfaceDefinition,
        ResponseDtoDefinition ...$responseDtoDefinitions
    ) : RequestHandlerInterfaceDefinition {
        $requestHandlerInterfaceNamesapce = $this->namingStrategy->buildNamespace($this->apiNamespace(), $this->operationName());
        $requestHandlerInterfaceClassName = $this->namingStrategy->stringToNamespace($this->operationName());
        $requestHandlerInterfaceMethod    = $this->namingStrategy->stringToMethodName($this->operationId());
        $requestHandlerInterfacePath      = $this->namingStrategy->buildPath($this->apiPath(), $this->operationName());
        $requestHandlerInterfaceFileName  = $requestHandlerInterfaceClassName . '.php';

        $requestHandlerInterfaceDefinition = new RequestHandlerInterfaceDefinition(
            $requestHandlerInterfacePath,
            $requestHandlerInterfaceFileName,
            $requestHandlerInterfaceNamesapce,
            $requestHandlerInterfaceClassName,
            $requestHandlerInterfaceMethod
        );
        $requestHandlerInterfaceDefinition->setSummary($summary);
        $requestHandlerInterfaceDefinition->setRequestDtoDefinition($requestDtoDefinition);
        $requestHandlerInterfaceDefinition->setResponseDtoDefinitions(...$responseDtoDefinitions);
        $requestHandlerInterfaceDefinition->setResponseDtoMarkerInterfaceDefinition(
            $responseDtoMarkerInterfaceDefinition
        );

        return $requestHandlerInterfaceDefinition;
    }
}
