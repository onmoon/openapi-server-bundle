<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator\Definitions;

class ComponentDefinition
{
    private DtoDefinition $dto;

    public function __construct(private string $name)
    {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDto(): DtoDefinition
    {
        return $this->dto;
    }

    public function setDto(DtoDefinition $dto): self
    {
        $this->dto = $dto;

        return $this;
    }
}
