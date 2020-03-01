<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions;

use cebe\openapi\spec\Schema;

class PropertyDtoDefinition extends SchemaBasedDtoDefinition
{
    private string $propertyName;

    public function __construct(
        string $directoryPath,
        string $fileName,
        string $namespace,
        string $className,
        Schema $schema,
        string $propertyName
    ) {
        parent::__construct($directoryPath, $fileName, $namespace, $className, $schema);

        $this->propertyName = $propertyName;
    }

    public function propertyName() : string
    {
        return $this->propertyName;
    }
}
