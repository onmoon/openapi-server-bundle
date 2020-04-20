<?php


namespace OnMoon\OpenApiServerBundle\CodeGenerator\Definitions;


class GeneratedInterfaceDefinition extends GeneratedClassDefinition
{
    private ?ClassDefinition $extends = null;

    /**
     * @return ClassDefinition|null
     */
    public function getExtends(): ?ClassDefinition
    {
        return $this->extends;
    }

    /**
     * @param ClassDefinition|null $extends
     * @return GeneratedInterfaceDefinition
     */
    public function setExtends(?ClassDefinition $extends): self
    {
        $this->extends = $extends;
        return $this;
    }

}