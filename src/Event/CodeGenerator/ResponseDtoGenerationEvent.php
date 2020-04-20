<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Event\CodeGenerator;

use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\ResponseDtoDefinition;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * The ResponseDtoGenerationEvent event occurs before the response
 * dto is generated.
 *
 * This event allows you to modify the definitions of the generated
 * response DTOs customizing the generated code.
 */
class ResponseDtoGenerationEvent extends Event
{
    private ResponseDtoDefinition $definition;

    public function __construct(ResponseDtoDefinition $definition)
    {
        $this->definition = $definition;
    }

    public function definition() : ResponseDtoDefinition
    {
        return $this->definition;
    }
}
