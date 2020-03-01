<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions;

use cebe\openapi\spec\Schema;

class ResponseDtoDefinition extends SchemaBasedDtoDefinition
{
    private string $responseCode;
    private ?ResponseDtoMarkerInterfaceDefinition $markerInterfaceDefinition;

    public function __construct(
        string $directoryPath,
        string $fileName,
        string $namespace,
        string $className,
        string $responseCode,
        Schema $schema
    ) {
        parent::__construct($directoryPath, $fileName, $namespace, $className, $schema);

        $this->responseCode              = $responseCode;
        $this->markerInterfaceDefinition = null;
    }

    public function responseCode() : string
    {
        return $this->responseCode;
    }

    public function markerInterfaceDefintion() : ?ResponseDtoMarkerInterfaceDefinition
    {
        return $this->markerInterfaceDefinition;
    }

    public function setMarkerInterfaceDefinition(ResponseDtoMarkerInterfaceDefinition $definition) : void
    {
        $this->markerInterfaceDefinition = $definition;
    }
}
