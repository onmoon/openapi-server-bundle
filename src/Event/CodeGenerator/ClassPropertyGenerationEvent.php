<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Event\CodeGenerator;

use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\BaseDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions\ClassPropertyDefinition;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * The ClassPropertyGenerationEvent event occurs before a class property
 * is generated in RequestBody/RequestParameters/Resposne DTO's.
 *
 * This event allows you to modify the definitions of the generated
 * class properties in RequestBody/RequestParameters/Resposne DTO's
 * customizing the generated code.
 */
class ClassPropertyGenerationEvent extends Event
{
    private BaseDefinition $classDefinition;
    private ClassPropertyDefinition $propertyDefinition;

    public function __construct(BaseDefinition $classDefinition, ClassPropertyDefinition $propertyDefinition)
    {
        $this->classDefinition    = $classDefinition;
        $this->propertyDefinition = $propertyDefinition;
    }

    public function classDefinition() : BaseDefinition
    {
        return $this->classDefinition;
    }

    public function propertyDefinition() : ClassPropertyDefinition
    {
        return $this->propertyDefinition;
    }
}
