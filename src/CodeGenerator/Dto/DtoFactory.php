<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator\Dto;

use OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions\RequestParametersDtoDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions\ResponseDtoMarkerInterfaceDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions\SchemaBasedDtoDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\GeneratedClass;

interface DtoFactory
{
    /**
     * @return GeneratedClass[]
     */
    public function generateDtoClassGraph(SchemaBasedDtoDefinition $definition) : array;

    public function generateRequestParametersDto(RequestParametersDtoDefinition $definition) : GeneratedClass;

    public function generateResponseMarkerInterface(ResponseDtoMarkerInterfaceDefinition $definition) : GeneratedClass;
}
