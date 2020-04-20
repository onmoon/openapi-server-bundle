<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator\Definitions;

class ClassDefinition
{
    private ?string $className = null;
    private ?string $namespace = null;

    public function getClassName() : ?string
    {
        return $this->className;
    }

    public function setClassName(?string $className) : self
    {
        $this->className = $className;

        return $this;
    }

    public function getNamespace() : ?string
    {
        return $this->namespace;
    }

    public function setNamespace(?string $namespace) : self
    {
        $this->namespace = $namespace;

        return $this;
    }

    public function getFQCN() : ?string
    {
        if ($this->className === null) {
            return null;
        }

        return ($this->namespace ?? '') . '\\' . $this->className;
    }
}
