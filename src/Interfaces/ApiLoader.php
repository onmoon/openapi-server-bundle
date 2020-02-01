<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Interfaces;

interface ApiLoader
{
    public function get(string $interfaceName) : void;
}
