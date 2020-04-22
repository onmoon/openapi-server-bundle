<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Interfaces;

interface Dto
{
    public function toArray() : array;
}
