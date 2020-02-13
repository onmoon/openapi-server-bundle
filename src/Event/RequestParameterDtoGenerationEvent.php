<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Event;

use OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions\RequestParametersDtoDefinition;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * The RequestParameterDtoGenerationEvent event occurs before the path
 * and query parameters DTO's are generated for the Request Dto.
 *
 * This event allows you to modify the definitions of the generated
 * Parameters DTO's customizing the generated code.
 *
 * The parametersType() method returns either "path" or "query" and
 * indicates what part of the RequestDTO is the ParameterDTO
 * generated for.
 */
class RequestParameterDtoGenerationEvent extends Event
{
    private RequestParametersDtoDefinition $definition;
    private string $parametersType;

    public function __construct(
        RequestParametersDtoDefinition $definition,
        string $parametersType
    ) {
        $this->definition     = $definition;
        $this->parametersType = $parametersType;
    }

    public function definition() : RequestParametersDtoDefinition
    {
        return $this->definition;
    }

    public function parametersType() : string
    {
        return $this->parametersType;
    }
}
