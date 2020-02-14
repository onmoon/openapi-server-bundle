<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions;

class ClassPropertyDefinition
{
    private string $name;
    private string $type;
    private bool $nullable;
    /** @var string|int|float|bool|null $defaultValue */
    private $defaultValue;
    private ?string $iterableType;
    private ?string $description;

    public function __construct(
        string $name,
        string $type
    ) {
        $this->name         = $name;
        $this->type         = $type;
        $this->nullable     = false;
        $this->defaultValue = null;
        $this->iterableType = null;
        $this->description  = null;
    }

    public function name() : string
    {
        return $this->name;
    }

    public function setName(string $name) : void
    {
        $this->name = $name;
    }

    public function type() : string
    {
        return $this->type;
    }

    public function setType(string $type) : void
    {
        $this->type = $type;
    }

    public function isNullable() : bool
    {
        return $this->nullable === true;
    }

    public function makeNullable() : void
    {
        $this->nullable = true;
    }

    public function makeNotNullable() : void
    {
        $this->nullable = false;
    }

    /**
     * @return string|int|float|bool|null $defaultValue
     */
    public function defaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * @param string|int|float|bool|null $defaultValue
     */
    public function setDefaultValue($defaultValue) : void
    {
        $this->defaultValue = $defaultValue;
    }

    public function iterableType() : ?string
    {
        return $this->iterableType;
    }

    public function setIterableType(?string $iterableType) : void
    {
        $this->iterableType = $iterableType;
    }

    public function description() : ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description) : void
    {
        $this->description = $description;
    }
}
