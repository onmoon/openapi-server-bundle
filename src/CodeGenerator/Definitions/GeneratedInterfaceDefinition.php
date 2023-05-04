<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator\Definitions;

class GeneratedInterfaceDefinition extends GeneratedClassDefinition
{
    private ?ClassReference $extends = null;

    final public function getExtends(): ?ClassReference
    {
        return $this->extends;
    }

    /**
     * @return GeneratedInterfaceDefinition
     */
    final public function setExtends(?ClassReference $extends): self
    {
        $this->extends = $extends;

        return $this;
    }
}
