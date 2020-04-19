<?php


namespace OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions;


class ClassDefinition
{
    private ?string $className = null;
    private ?string $namespace = null;

    /**
     * @return string|null
     */
    public function getClassName(): ?string
    {
        return $this->className;
    }

    /**
     * @param string|null $className
     * @return InterfaceDefinition
     */
    public function setClassName(?string $className): self
    {
        $this->className = $className;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getNamespace(): ?string
    {
        return $this->namespace;
    }

    /**
     * @param string|null $namespace
     * @return InterfaceDefinition
     */
    public function setNamespace(?string $namespace): self
    {
        $this->namespace = $namespace;
        return $this;
    }

    public function getFQCN(): ?string {
        if($this->className === null) {
            return null;
        }

        return ($this->namespace ?? '') . '\\' . $this->className;
    }

}
