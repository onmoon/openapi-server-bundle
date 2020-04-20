<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator\Definitions;

class GeneratedInterfaceDefinition extends GeneratedClassDefinition
{
    private ?ClassDefinition $extends = null;

    public function getExtends() : ?ClassDefinition
    {
        return $this->extends;
    }

    /**
     * @return GeneratedInterfaceDefinition
     */
    public function setExtends(?ClassDefinition $extends) : self
    {
        $this->extends = $extends;

        return $this;
    }
}
