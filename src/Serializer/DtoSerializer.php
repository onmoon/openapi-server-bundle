<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Serializer;

use OnMoon\OpenApiServerBundle\Interfaces\Dto;
use OnMoon\OpenApiServerBundle\Specification\Definitions\ObjectSchema;
use OnMoon\OpenApiServerBundle\Specification\Definitions\Operation;
use Symfony\Component\HttpFoundation\Request;

interface DtoSerializer
{
    /**
     * @psalm-param class-string<Dto> $inputDtoFQCN
     */
    public function createRequestDto(
        Request $request,
        Operation $operation,
        string $inputDtoFQCN
    ): Dto;

    /** @return mixed[] */
    public function createResponseFromDto(Dto $responseDto, ObjectSchema $definition): array;
}
