<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Event\CodeGenerator;

use OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions\ResponseDtoMarkerInterfaceDefinition;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * The ResponseDtoMarkerInterfaceGenerationEvent event occurs before the response
 * DTO marker interface is generated. This marker interface is used for typehinting
 * if a Request Handler returns multiple possible Response DTO's.
 *
 * This event allows you to modify the definition of the generated
 * response DTO marker interface customizing the generated code.
 */
class ResponseDtoMarkerInterfaceGenerationEvent extends Event
{
    private ResponseDtoMarkerInterfaceDefinition $definition;

    public function __construct(ResponseDtoMarkerInterfaceDefinition $definition)
    {
        $this->definition = $definition;
    }

    public function definition() : ResponseDtoMarkerInterfaceDefinition
    {
        return $this->definition;
    }
}
