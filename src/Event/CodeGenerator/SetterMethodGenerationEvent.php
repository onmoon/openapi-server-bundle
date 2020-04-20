<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Event\CodeGenerator;

use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\BaseDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\SetterMethodDefinition;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * The SetterMethodGenerationEvent event occurs before a setter method
 * is generated in RequestBody/RequestParameters/Resposne DTO's
 *
 * This event allows you to modify the definitions of the generated
 * setter methods in RequestBody/RequestParameters/Resposne DTO's
 * customizing the generated code
 */
class SetterMethodGenerationEvent extends Event
{
    private BaseDefinition $classDefinition;
    private SetterMethodDefinition $methodDefinition;

    public function __construct(BaseDefinition $classDefinition, SetterMethodDefinition $methodDefinition)
    {
        $this->classDefinition  = $classDefinition;
        $this->methodDefinition = $methodDefinition;
    }

    public function classDefinition() : BaseDefinition
    {
        return $this->classDefinition;
    }

    public function propertyDefinition() : SetterMethodDefinition
    {
        return $this->methodDefinition;
    }
}
