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
    public function toArray(): array;

    /**
     * Generate Dto tree from normalized array
     *
     * @param mixed[] $data
     */
    public static function fromArray(array $data): self;
}
