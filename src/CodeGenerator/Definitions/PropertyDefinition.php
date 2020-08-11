<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator\Definitions;

use OnMoon\OpenApiServerBundle\Specification\Definitions\Property;

final class PropertyDefinition
{
    private Property $specProperty;
    private string $classPropertyName;
    private bool $nullable                       = true;
    private ?DtoDefinition $objectTypeDefinition = null;
    private ?string $getterName                  = null;
    private ?string $setterName                  = null;
    private bool $hasGetter                      = false;
    private bool $hasSetter                      = false;
    private bool $inConstructor                  = false;

    public function __construct(Property $specProperty)
    {
        $this->specProperty = $specProperty;
    }

    public function getDescription(): ?string
    {
        return $this->specProperty->getDescription();
    }

    public function getSpecProperty(): Property
    {
        return $this->specProperty;
    }

    public function getClassPropertyName(): string
    {
        return $this->classPropertyName;
    }

    public function setClassPropertyName(string $classPropertyName): self
    {
        $this->classPropertyName = $classPropertyName;

        return $this;
    }

    public function getSpecPropertyName(): string
    {
        return $this->specProperty->getName();
    }

    public function isArray(): bool
    {
        return $this->specProperty->isArray();
    }

    public function getScalarTypeId(): ?int
    {
        return $this->specProperty->getScalarTypeId();
    }

    public function getObjectTypeDefinition(): ?DtoDefinition
    {
        return $this->objectTypeDefinition;
    }

    public function setObjectTypeDefinition(?DtoDefinition $objectTypeDefinition): self
    {
        $this->objectTypeDefinition = $objectTypeDefinition;

        return $this;
    }

    public function isNullable(): bool
    {
        return $this->nullable;
    }

    public function setNullable(bool $nullable): self
    {
        $this->nullable = $nullable;

        return $this;
    }

    public function getGetterName(): ?string
    {
        return $this->getterName;
    }

    public function setGetterName(?string $getterName): self
    {
        $this->getterName = $getterName;

        return $this;
    }

    public function getSetterName(): ?string
    {
        return $this->setterName;
    }

    public function setSetterName(?string $setterName): self
    {
        $this->setterName = $setterName;

        return $this;
    }

    public function hasGetter(): bool
    {
        return $this->hasGetter;
    }

    public function setHasGetter(bool $hasGetter): self
    {
        $this->hasGetter = $hasGetter;

        return $this;
    }

    public function hasSetter(): bool
    {
        return $this->hasSetter;
    }

    public function setHasSetter(bool $hasSetter): self
    {
        $this->hasSetter = $hasSetter;

        return $this;
    }

    public function isInConstructor(): bool
    {
        return $this->inConstructor;
    }

    public function setInConstructor(bool $inConstructor): self
    {
        $this->inConstructor = $inConstructor;

        return $this;
    }
}
