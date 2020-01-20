<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Serializer;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Route;

interface DtoSerializer
{
    public function createRequestDto(
        Request $request,
        Route $route,
        string $serviceInterface,
        string $methodName
    ) : ?object;

    public function createResponse(object $dto) : string;
}
