<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions;

class PropertyDefinition
{
    private string $specPropertyName;
    private string $classPropertyName;
    private bool $array = false;
    private $defaultValue = null;
    private bool $required = false;
    private bool $nullable = true;
    private ?int $scalarTypeId = null;
    private ?DtoDefinition $objectTypeDefinition = null;
    private ?string $description = null;
    private ?string $getterName = null;
    private ?string $setterName = null;

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     * @return PropertyDefinition
     */
    public function setDescription(?string $description): PropertyDefinition
    {
        $this->description = $description;
        return $this;
    }

    /**
     * PropertyDtoDefinition constructor.
     * @param string $specPropertyName
     */
    public function __construct(string $specPropertyName)
    {
        $this->specPropertyName = $specPropertyName;
        $this->classPropertyName = $specPropertyName;
    }

    /**
     * @return string
     */
    public function getSpecPropertyName(): string
    {
        return $this->specPropertyName;
    }

    /**
     * @return string
     */
    public function getClassPropertyName(): string
    {
        return $this->classPropertyName;
    }

    /**
     * @param string $classPropertyName
     * @return PropertyDefinition
     */
    public function setClassPropertyName(string $classPropertyName): PropertyDefinition
    {
        $this->classPropertyName = $classPropertyName;
        return $this;
    }

    /**
     * @return bool
     */
    public function isArray(): bool
    {
        return $this->array;
    }

    /**
     * @param bool $array
     * @return PropertyDefinition
     */
    public function setArray(bool $array): PropertyDefinition
    {
        $this->array = $array;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * @param mixed $defaultValue
     * @return PropertyDefinition
     */
    public function setDefaultValue($defaultValue)
    {
        $this->defaultValue = $defaultValue;
        return $this;
    }

    /**
     * @return bool
     */
    public function isRequired(): bool
    {
        return $this->required;
    }

    /**
     * @param bool $required
     * @return PropertyDefinition
     */
    public function setRequired(bool $required): PropertyDefinition
    {
        $this->required = $required;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getScalarTypeId(): ?int
    {
        return $this->scalarTypeId;
    }

    /**
     * @param int|null $scalarTypeId
     * @return PropertyDefinition
     */
    public function setScalarTypeId(?int $scalarTypeId): PropertyDefinition
    {
        $this->scalarTypeId = $scalarTypeId;
        return $this;
    }

    public function getObjectTypeDefinition(): ?DtoDefinition
    {
        return $this->objectTypeDefinition;
    }

    public function setObjectTypeDefinition(DtoDefinition $objectTypeDefinition): PropertyDefinition
    {
        $this->objectTypeDefinition = $objectTypeDefinition;
        return $this;
    }

    /**
     * @return bool
     */
    public function isNullable(): bool
    {
        return $this->nullable;
    }

    /**
     * @param bool $nullable
     * @return PropertyDefinition
     */
    public function setNullable(bool $nullable): PropertyDefinition
    {
        $this->nullable = $nullable;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getGetterName(): ?string
    {
        return $this->getterName;
    }

    /**
     * @param string|null $getterName
     * @return PropertyDefinition
     */
    public function setGetterName(?string $getterName): PropertyDefinition
    {
        $this->getterName = $getterName;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getSetterName(): ?string
    {
        return $this->setterName;
    }

    /**
     * @param string|null $setterName
     * @return PropertyDefinition
     */
    public function setSetterName(?string $setterName): PropertyDefinition
    {
        $this->setterName = $setterName;
        return $this;
    }

}
