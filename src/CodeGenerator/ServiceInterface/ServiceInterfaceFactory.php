<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator\ServiceInterface;

use OnMoon\OpenApiServerBundle\CodeGenerator\GeneratedClass;

interface ServiceInterfaceFactory
{
    public function generateServiceInterface(
        string $fileDirectoryPath,
        string $fileName,
        string $namespace,
        string $className,
        string $methodName,
        ?string $summary = null,
        ?string $inputDtoNamespace = null,
        ?string $inputDtoClassName = null,
        ?string $outputDtoNamespace = null,
        ?string $outputDtoClassName = null
    ) : GeneratedClass;
}
