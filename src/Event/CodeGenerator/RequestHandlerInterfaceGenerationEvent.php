<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Event\CodeGenerator;

use OnMoon\OpenApiServerBundle\CodeGenerator\RequestHandlerInterface\Definitions\RequestHandlerInterfaceDefinition;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * The RequestHandlerInterfaceGenerationEvent event occurs before the request
 * handler interface is generated.
 *
 * This event allows you to modify the definitions of the generated
 * request handler interfaces customizing the generated code.
 */
class RequestHandlerInterfaceGenerationEvent extends Event
{
    private RequestHandlerInterfaceDefinition $definition;

    public function __construct(RequestHandlerInterfaceDefinition $definition)
    {
        $this->definition = $definition;
    }

    public function definition() : RequestHandlerInterfaceDefinition
    {
        return $this->definition;
    }
}
