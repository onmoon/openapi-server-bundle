<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Specification\Definitions;

final class Property
{
    private string $name;

    private bool $array = false;
    /** @var string|int|float|bool|null  */
    private $defaultValue                       = null;
    private bool $required                      = false;
    private bool $nullable                      = false;
    private ?int $scalarTypeId                  = null;
    private ObjectSchema|ObjectReference|null $objectTypeDefinition = null;
    private ?string $description                = null;
    private ?string $pattern                    = null;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isArray(): bool
    {
        return $this->array;
    }

    /**
     * @return Property
     */
    public function setArray(bool $array): self
    {
        $this->array = $array;

        return $this;
    }

    /**
     * @return bool|float|int|string|null
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * @param bool|float|int|string|null $defaultValue
     */
    public function setDefaultValue($defaultValue): Property
    {
        $this->defaultValue = $defaultValue;

        return $this;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    /**
     * @return Property
     */
    public function setRequired(bool $required): self
    {
        $this->required = $required;

        return $this;
    }

    public function getScalarTypeId(): ?int
    {
        return $this->scalarTypeId;
    }

    /**
     * @return Property
     */
    public function setScalarTypeId(?int $scalarTypeId): self
    {
        $this->scalarTypeId = $scalarTypeId;

        return $this;
    }

    public function getObjectTypeDefinition(): ObjectSchema|ObjectReference|null
    {
        return $this->objectTypeDefinition;
    }

    /**
     * @return Property
     */
    public function setObjectTypeDefinition(ObjectSchema|ObjectReference|null $objectTypeDefinition): self
    {
        $this->objectTypeDefinition = $objectTypeDefinition;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @return Property
     */
    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getPattern(): ?string
    {
        return $this->pattern;
    }

    /**
     * @return Property
     */
    public function setPattern(?string $pattern): self
    {
        $this->pattern = $pattern;

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
}
