<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Event\CodeGenerator;

use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\BaseDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\GetterMethodDefinition;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * The GetterMethodGenerationEvent event occurs before a getter method
 * is generated in RequestBody/RequestParameters/Resposne DTO's
 *
 * This event allows you to modify the definitions of the generated
 * getter methods in RequestBody/RequestParameters/Resposne DTO's
 * customizing the generated code
 */
class GetterMethodGenerationEvent extends Event
{
    private BaseDefinition $classDefinition;
    private GetterMethodDefinition $methodDefinition;

    public function __construct(BaseDefinition $classDefinition, GetterMethodDefinition $methodDefinition)
    {
        $this->classDefinition  = $classDefinition;
        $this->methodDefinition = $methodDefinition;
    }

    public function classDefinition() : BaseDefinition
    {
        return $this->classDefinition;
    }

    public function propertyDefinition() : GetterMethodDefinition
    {
        return $this->methodDefinition;
    }
}
