<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator\Definitions;

interface ClassReference
{
    public function getClassName(): string;

    public function getNamespace(): string;

    public function getFQCN(): string;
}
