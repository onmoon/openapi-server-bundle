<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Event\CodeGenerator;

use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\BaseDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions\MethodParameterDefinition;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * The ConstructorParameterGenerationEvent event occurs before a constructors parameter
 * is generated in RequestBody/RequestParameters/Resposne DTO's.
 *
 * This event allows you to modify the definitions of the generated
 * constructors parameters in RequestBody/RequestParameters/Resposne DTO's
 * customizing the generated code.
 */
class ConstructorParameterGenerationEvent extends Event
{
    private BaseDefinition $classDefinition;
    private MethodParameterDefinition $parameterDefinition;

    public function __construct(BaseDefinition $classDefinition, MethodParameterDefinition $parameterDefinition)
    {
        $this->classDefinition     = $classDefinition;
        $this->parameterDefinition = $parameterDefinition;
    }

    public function classDefinition() : BaseDefinition
    {
        return $this->classDefinition;
    }

    public function parameterDefinition() : MethodParameterDefinition
    {
        return $this->parameterDefinition;
    }
}
