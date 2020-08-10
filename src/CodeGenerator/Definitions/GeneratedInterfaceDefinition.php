<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator\Definitions;

class GeneratedInterfaceDefinition extends GeneratedClassDefinition
{
    private ?ClassDefinition $extends = null;

    final public function getExtends(): ?ClassDefinition
    {
        return $this->extends;
    }

    /**
     * @return GeneratedInterfaceDefinition
     */
    final public function setExtends(?ClassDefinition $extends): self
    {
        $this->extends = $extends;

        return $this;
    }
}
