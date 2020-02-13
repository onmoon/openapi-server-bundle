<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Serializer;

use OnMoon\OpenApiServerBundle\Interfaces\Dto;
/** phpcs:disable SlevomatCodingStandard.Namespaces.UnusedUses.UnusedUse */
use OnMoon\OpenApiServerBundle\Interfaces\Service;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Route;

interface DtoSerializer
{
    /**
     * @psalm-param class-string<Service> $serviceInterface
     */
    public function createRequestDto(
        Request $request,
        Route $route,
        string $serviceInterface,
        string $methodName
    ) : ?Dto;

    public function createResponse(object $dto) : string;
}
