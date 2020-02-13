<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions\Factory;

use OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions\ResponseDtoMarkerInterfaceDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Factory\OperationDefinitionFactory;

class ResponseDtoMarkerInterfaceDefinitionFactory extends OperationDefinitionFactory
{
    public function create() : ResponseDtoMarkerInterfaceDefinition
    {
        $responseDtoMarkerInterfaceNamespace = $this->namingStrategy->buildNamespace(
            $this->operationNamespace(),
            self::DTO_NAMESPACE,
            self::RESPONSE_SUFFIX
        );
        $responseDtoMarkerInterfaceClassName = $this->namingStrategy->stringToNamespace(
            $this->operationName() . self::RESPONSE_SUFFIX
        );
        $responseDtoMarkerInterfacePath      = $this->namingStrategy->buildPath(
            $this->operationPath(),
            self::DTO_NAMESPACE,
            self::RESPONSE_SUFFIX
        );
        $responseDtoMarkerInterfaceFileName  = $responseDtoMarkerInterfaceClassName . '.php';

        return new ResponseDtoMarkerInterfaceDefinition(
            $responseDtoMarkerInterfacePath,
            $responseDtoMarkerInterfaceFileName,
            $responseDtoMarkerInterfaceNamespace,
            $responseDtoMarkerInterfaceClassName
        );
    }
}
