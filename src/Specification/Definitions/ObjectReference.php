<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Specification\Definitions;

use Override;

final class ObjectReference implements GetSchema
{
    public function __construct(private string $schemaName, private ObjectSchema $referencedObject)
    {
    }

    public function getSchemaName(): string
    {
        return $this->schemaName;
    }

    public function getReferencedObject(): ObjectSchema
    {
        return $this->referencedObject;
    }

    #[Override]
    public function getSchema(): ObjectSchema
    {
        return $this->getReferencedObject();
    }
}
