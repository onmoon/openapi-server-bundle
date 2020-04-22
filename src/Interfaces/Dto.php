<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Interfaces;

interface Dto
{
    /**
     * Generate normalized array from Dto tree
     *
     * @return mixed[]
     */
    public function toArray() : array;
}
