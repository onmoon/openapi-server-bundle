<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator\Definitions;

use OnMoon\OpenApiServerBundle\Interfaces\ApiLoader;

final class ServiceSubscriberDefinition extends GeneratedClassDefinition
{
    /** @var ClassReference[] */
    private array $implements;

    public function __construct()
    {
        $this->implements = [ClassDefinition::fromFQCN(ApiLoader::class)];
    }

    /**
     * @return ClassReference[]
     */
    public function getImplements(): array
    {
        return $this->implements;
    }

    /**
     * @param ClassReference[] $implements
     */
    public function setImplements(array $implements): self
    {
        $this->implements = $implements;

        return $this;
    }
}
