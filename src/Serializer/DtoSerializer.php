<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Serializer;

use OnMoon\OpenApiServerBundle\Interfaces\Dto;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Route;

interface DtoSerializer
{
    /**
     * @psalm-param class-string<Dto> $inputDtoFQCN
     */
    public function createRequestDto(
        Request $request,
        Route $route,
        string $inputDtoFQCN
    ) : Dto;
}
