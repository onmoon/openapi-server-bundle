<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions;

use cebe\openapi\spec\Schema;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\BaseDefinition;

class SchemaBasedDtoDefinition extends BaseDefinition
{
    private Schema $schema;
    private bool $immutable;

    public function __construct(
        string $directoryPath,
        string $fileName,
        string $namespace,
        string $className,
        Schema $schema
    ) {
        parent::__construct($directoryPath, $fileName, $namespace, $className);

        $this->schema    = $schema;
        $this->immutable = false;
    }

    public function schema() : Schema
    {
        return $this->schema;
    }

    public function makeImmutable() : void
    {
        $this->immutable = true;
    }

    public function makeMutable() : void
    {
        $this->immutable = false;
    }

    public function isImmutable() : bool
    {
        return $this->immutable === true;
    }
}
