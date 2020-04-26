<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator\Definitions;

class PropertyDefinition
{
    private string $specPropertyName;
    private string $classPropertyName;
    private bool $array = false;
    /** @var string|int|float|bool|null  */
    private $defaultValue                        = null;
    private bool $required                       = false;
    private bool $nullable                       = true;
    private ?int $scalarTypeId                   = null;
    private ?DtoDefinition $objectTypeDefinition = null;
    private ?string $description                 = null;
    private ?string $getterName                  = null;
    private ?string $setterName                  = null;
    private bool $hasGetter                      = false;
    private bool $hasSetter                      = false;
    private bool $inConstructor                  = false;

    public function getDescription() : ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description) : self
    {
        $this->description = $description;

        return $this;
    }

    public function __construct(string $specPropertyName)
    {
        $this->specPropertyName = $specPropertyName;
    }

    public function getSpecPropertyName() : string
    {
        return $this->specPropertyName;
    }

    public function getClassPropertyName() : string
    {
        return $this->classPropertyName;
    }

    public function setClassPropertyName(string $classPropertyName) : self
    {
        $this->classPropertyName = $classPropertyName;

        return $this;
    }

    public function isArray() : bool
    {
        return $this->array;
    }

    public function setArray(bool $array) : self
    {
        $this->array = $array;

        return $this;
    }

    /**
     * @return string|int|float|bool|null
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * @param string|int|float|bool|null $defaultValue
     */
    public function setDefaultValue($defaultValue) : self
    {
        $this->defaultValue = $defaultValue;

        return $this;
    }

    public function isRequired() : bool
    {
        return $this->required;
    }

    public function setRequired(bool $required) : self
    {
        $this->required = $required;

        return $this;
    }

    public function getScalarTypeId() : ?int
    {
        return $this->scalarTypeId;
    }

    public function setScalarTypeId(?int $scalarTypeId) : self
    {
        $this->scalarTypeId = $scalarTypeId;

        return $this;
    }

    public function getObjectTypeDefinition() : ?DtoDefinition
    {
        return $this->objectTypeDefinition;
    }

    public function setObjectTypeDefinition(?DtoDefinition $objectTypeDefinition) : self
    {
        $this->objectTypeDefinition = $objectTypeDefinition;

        return $this;
    }

    public function isNullable() : bool
    {
        return $this->nullable;
    }

    public function setNullable(bool $nullable) : self
    {
        $this->nullable = $nullable;

        return $this;
    }

    public function getGetterName() : ?string
    {
        return $this->getterName;
    }

    public function setGetterName(?string $getterName) : self
    {
        $this->getterName = $getterName;

        return $this;
    }

    public function getSetterName() : ?string
    {
        return $this->setterName;
    }

    public function setSetterName(?string $setterName) : self
    {
        $this->setterName = $setterName;

        return $this;
    }

    public function hasGetter() : bool
    {
        return $this->hasGetter;
    }

    public function setHasGetter(bool $hasGetter) : self
    {
        $this->hasGetter = $hasGetter;

        return $this;
    }

    public function hasSetter() : bool
    {
        return $this->hasSetter;
    }

    public function setHasSetter(bool $hasSetter) : self
    {
        $this->hasSetter = $hasSetter;

        return $this;
    }

    public function isInConstructor() : bool
    {
        return $this->inConstructor;
    }

    public function setInConstructor(bool $inConstructor) : self
    {
        $this->inConstructor = $inConstructor;

        return $this;
    }
}
