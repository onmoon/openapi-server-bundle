<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator\Definitions;

use Override;

final class ComponentReference implements DtoReference
{
    public function __construct(private ComponentDefinition $referencedComponent)
    {
    }

    #[Override]
    public function getClassName(): string
    {
        return $this->referencedComponent->getDto()->getClassName();
    }

    #[Override]
    public function getNamespace(): string
    {
        return $this->referencedComponent->getDto()->getNamespace();
    }

    #[Override]
    public function getFQCN(): string
    {
        return $this->referencedComponent->getDto()->getFQCN();
    }

    #[Override]
    public function isEmpty(): bool
    {
        return $this->referencedComponent->getDto()->isEmpty();
    }
}
