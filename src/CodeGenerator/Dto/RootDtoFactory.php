<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator\Dto;

use cebe\openapi\spec\Parameter;
use OnMoon\OpenApiServerBundle\CodeGenerator\GeneratedClass;

interface RootDtoFactory
{
    /**
     * @param Parameter[] $pathParameters
     * @param Parameter[] $queryParameters
     *
     * @return GeneratedClass[]
     */
    public function generateRootDto(
        string $fileDirectoryPath,
        string $fileName,
        string $namespace,
        string $className,
        ?string $requestBodyDtoNamespace = null,
        ?string $requestBodyDtoClassName = null,
        array $pathParameters = [],
        array $queryParameters = []
    ) : array;
}
