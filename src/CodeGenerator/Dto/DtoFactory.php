<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator\Dto;

use cebe\openapi\spec\Parameter;
use OnMoon\OpenApiServerBundle\CodeGenerator\GeneratedClass;
use cebe\openapi\spec\Schema;

interface DtoFactory
{
    /**
     * @return GeneratedClass[]
     */
    public function generateDtoClassGraph(
        string $fileDirectoryPath,
        string $fileName,
        string $namespace,
        string $className,
        bool $immutable,
        Schema $schema
    ) : array;

    /**
     * @param Parameter[] $parameters
     */
    public function generateParamDto(
        string $fileDirectoryPath,
        string $fileName,
        string $namespace,
        string $className,
        array $parameters
    ) : GeneratedClass;
}
