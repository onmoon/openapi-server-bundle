<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Event\CodeGenerator;

use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\PropertyDtoDefinition;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * The PropertyDtoGenerationEvent event occurs before any
 * of the DTOs nested in the Request Body or Response are generated.
 *
 * This event allows you to modify the definitions of the generated
 * nested DTOs that are used in the Request Body and Response DTOs,
 * customizing the generated code.
 */
class PropertyDtoGenerationEvent extends Event
{
    private PropertyDtoDefinition $definition;

    public function __construct(PropertyDtoDefinition $definition)
    {
        $this->definition = $definition;
    }

    public function definition() : PropertyDtoDefinition
    {
        return $this->definition;
    }
}
