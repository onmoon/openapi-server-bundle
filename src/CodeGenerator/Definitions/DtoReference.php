<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator\Definitions;

interface DtoReference extends ClassReference
{
    public function isEmpty(): bool;
}
