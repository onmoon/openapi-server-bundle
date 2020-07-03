<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Interfaces;

interface SetClientIp
{
    public function setClientIp(string $ip): void;
}
