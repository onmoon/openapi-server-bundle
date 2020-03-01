<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Event\CodeGenerator;

use OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions\RequestBodyDtoDefinition;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * The RequestBodyDtoGenerationEvent event occurs before the request
 * body dto is generated.
 *
 * This event allows you to modify the definitions of the generated
 * request body DTO customizing the generated code.
 */
class RequestBodyDtoGenerationEvent extends Event
{
    private RequestBodyDtoDefinition $definition;

    public function __construct(RequestBodyDtoDefinition $definition)
    {
        $this->definition = $definition;
    }

    public function definition() : RequestBodyDtoDefinition
    {
        return $this->definition;
    }
}
