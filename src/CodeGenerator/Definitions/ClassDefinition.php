<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator\Definitions;

use function Safe\substr;
use function strrpos;

class ClassDefinition
{
    private string $className;
    private string $namespace;

    public function getClassName(): int
    {
        return $this->className;
    }

    public function setClassName(string $className): self
    {
        $this->className = $className;

        return $this;
    }

    public function getNamespace(): string
    {
        return $this->namespace;
    }

    public function setNamespace(string $namespace): self
    {
        $this->namespace = $namespace;

        return $this;
    }

    public function getFQCN(): string
    {
        return $this->namespace . '\\' . $this->className;
    }

    public static function fromFQCN(string $className): ClassDefinition
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
