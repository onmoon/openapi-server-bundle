<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator\Definitions;

use function strrpos;

class ClassDefinition implements ClassReference
{
    private string $className;
    private string $namespace;

    final public function getClassName(): string
    {
        return $this->className;
    }

    final public function setClassName(string $className): self
    {
        $this->className = $className;

        return $this;
    }

    final public function getNamespace(): string
    {
        return $this->namespace;
    }

    final public function setNamespace(string $namespace): self
    {
        $this->namespace = $namespace;

        return $this;
    }

    final public function getFQCN(): string
    {
        return $this->namespace . '\\' . $this->className;
    }

    final public static function fromFQCN(string $className): ClassDefinition
    {
        $lastPart = strrpos($className, '\\');

        if ($lastPart !== false) {
            $namespace = substr($className, 0, $lastPart);
            $name      = substr($className, $lastPart + 1);
        } else {
            $namespace = '';
            $name      = $className;
        }

        return (new ClassDefinition())
            ->setNamespace($namespace)
            ->setClassName($name);
    }
}
