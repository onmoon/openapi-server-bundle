<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions;

use cebe\openapi\spec\Schema;

class ResponseDtoDefinition extends SchemaBasedDtoDefinition
{
    private ?int $responseCode;
    private ?ResponseDtoMarkerInterfaceDefinition $markerInterfaceDefinition;

    public function __construct(
        string $directoryPath,
        string $fileName,
        string $namespace,
        string $className,
        Schema $schema
    ) {
        parent::__construct($directoryPath, $fileName, $namespace, $className, $schema);

        $this->responseCode              = null;
        $this->markerInterfaceDefinition = null;
    }

    public function responseCode() : ?int
    {
        return $this->responseCode;
    }

    public function setResponseCode(int $responseCode) : void
    {
        $this->responseCode = $responseCode;
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
