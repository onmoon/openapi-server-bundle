<?php


namespace OnMoon\OpenApiServerBundle\Specification\Definitions;


class PropertyDefinition
{
    private string $name;

    private bool $array = false;
    /** @var string|int|float|bool|null  */
    private $defaultValue                           = null;
    private bool $required                          = false;
    private bool $nullable                          = true;
    private ?int $scalarTypeId                      = null;
    private ?ObjectDefinition $objectTypeDefinition = null;
    private ?string $description                    = null;
    private ?string $pattern                        = null;

    /**
     * PropertyDefinition constructor.
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
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
    public function setRequired(bool $required): self
    {
        $this->required = $required;
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
    public function setNullable(bool $nullable): self
    {
        $this->nullable = $nullable;
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
    public function setScalarTypeId(?int $scalarTypeId): self
    {
        $this->scalarTypeId = $scalarTypeId;
        return $this;
    }

    /**
     * @return ObjectDefinition|null
     */
    public function getObjectTypeDefinition(): ?ObjectDefinition
    {
        return $this->objectTypeDefinition;
    }

    /**
     * @param ObjectDefinition|null $objectTypeDefinition
     * @return PropertyDefinition
     */
    public function setObjectTypeDefinition(?ObjectDefinition $objectTypeDefinition): self
    {
        $this->objectTypeDefinition = $objectTypeDefinition;
        return $this;
    }

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
    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPattern(): ?string
    {
        return $this->pattern;
    }

    /**
     * @param string|null $pattern
     * @return PropertyDefinition
     */
    public function setPattern(?string $pattern): self
    {
        $this->pattern = $pattern;
        return $this;
    }


}
