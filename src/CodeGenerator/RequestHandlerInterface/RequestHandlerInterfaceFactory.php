<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator\RequestHandlerInterface;

use OnMoon\OpenApiServerBundle\CodeGenerator\GeneratedClass;
use OnMoon\OpenApiServerBundle\CodeGenerator\RequestHandlerInterface\Definitions\RequestHandlerInterfaceDefinition;

interface RequestHandlerInterfaceFactory
{
    public function generateInterface(RequestHandlerInterfaceDefinition $definition) : GeneratedClass;
}
