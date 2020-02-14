<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Serializer;

use OnMoon\OpenApiServerBundle\Interfaces\Dto;
/** phpcs:disable SlevomatCodingStandard.Namespaces.UnusedUses.UnusedUse */
use OnMoon\OpenApiServerBundle\Interfaces\RequestHandler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Route;

interface DtoSerializer
{
    /**
     * @psalm-param class-string<RequestHandler> $requestHandlerInterface
     */
    public function createRequestDto(
        Request $request,
        Route $route,
        string $requestHandlerInterface,
        string $methodName
    ) : ?Dto;

    public function createResponse(object $dto) : string;
}
