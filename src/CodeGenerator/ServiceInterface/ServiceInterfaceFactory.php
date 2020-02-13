<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator\ServiceInterface;

use OnMoon\OpenApiServerBundle\CodeGenerator\GeneratedClass;

interface ServiceInterfaceFactory
{
    /**
     * @param string[][] $outputDtos
     *
     * @psalm-param list<array{namespace: string, className: string, code: int}> $outputDtos
     */
    public function generateServiceInterface(
        string $fileDirectoryPath,
        string $fileName,
        string $namespace,
        string $className,
        string $methodName,
        ?string $summary = null,
        ?string $inputDtoNamespace = null,
        ?string $inputDtoClassName = null,
        array $outputDtos = [],
        ?string $outputDtoMarkerInterfaceNamespace = null,
        ?string $outputDtoMarkerInterfaceClassName = null
    ) : GeneratedClass;
}
