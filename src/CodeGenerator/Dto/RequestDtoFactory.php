<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator\Dto;

use OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions\RequestDtoDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\GeneratedClass;

interface RequestDtoFactory
{
    /**
     * @return GeneratedClass[]
     *
     * @psalm-return list<GeneratedClass>
     */
    public function generateDto(RequestDtoDefinition $definition) : array;
}
