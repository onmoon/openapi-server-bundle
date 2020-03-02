<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Interfaces;

interface GetResponseCode
{
    public function getResponseCode(?int $guessedCode) : ?int;
}
