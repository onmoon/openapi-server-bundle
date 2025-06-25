<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator\Definitions;

final class ComponentReference implements DtoReference
{
    public function __construct(private ComponentDefinition $referencedComponent)
    {
    }

    public function getClassName(): string
    {
        return $this->referencedComponent->getDto()->getClassName();
    }

    public function getNamespace(): string
    {
        return $this->referencedComponent->getDto()->getNamespace();
    }

    public function getFQCN(): string
    {
        return $this->referencedComponent->getDto()->getFQCN();
    }

    public function isEmpty(): bool
    {
        return $this->referencedComponent->getDto()->isEmpty();
    }
}
