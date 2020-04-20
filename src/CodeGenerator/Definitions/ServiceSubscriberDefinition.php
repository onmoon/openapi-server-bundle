<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator\Definitions;

class ServiceSubscriberDefinition extends GeneratedClassDefinition
{
    /** @var ClassDefinition[] */
    private array $implements = [];

    /**
     * @return ClassDefinition[]
     */
    public function getImplements() : array
    {
        return $this->implements;
    }

    /**
     * @param ClassDefinition[] $implements
     */
    public function setImplements(array $implements) : self
    {
        $this->implements = $implements;

        return $this;
    }
}
