<?php

namespace OnMoon\OpenApiServerBundle\CodeGenerator\Definitions;

interface DtoReference extends ClassReference
{
    public function isEmpty(): bool;
}