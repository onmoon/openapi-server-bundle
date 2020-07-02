<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Interfaces;

use Symfony\Component\HttpFoundation\Request;

interface SetRequest
{
    public function setRequest(Request $request): void;
}
