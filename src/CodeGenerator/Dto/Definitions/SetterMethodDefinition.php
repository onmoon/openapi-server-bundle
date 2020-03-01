<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator\Dto\Definitions;

class SetterMethodDefinition
{
    private string $name;
    private string $type;
    private ?string $iterableType;

    public function __construct(
        string $name,
        string $type
    ) {
        $this->name         = $name;
        $this->type         = $type;
        $this->iterableType = null;
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

    public function iterableType() : ?string
    {
        return $this->iterableType;
    }

    public function setIterableType(?string $iterableType) : void
    {
        $this->iterableType = $iterableType;
    }
}
