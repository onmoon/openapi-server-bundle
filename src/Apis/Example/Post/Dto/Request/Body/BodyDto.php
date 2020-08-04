<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Apis\Example\Post\Dto\Request\Body;

use OnMoon\OpenApiServerBundle\Interfaces\Dto;
/**
 * This class was automatically generated
 * You should not change it manually as it will be overwritten
 */

final class BodyDto implements Dto
{
    /**
     * Example name.
     */
    private string $name;
    /**
     * Example value.
     */
    private ?string $value = null;

    public function getName(): string
    {
        return $this->name;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    /** @inheritDoc */
    public function toArray(): array
    {
        return ['name' => $this->name, 'value' => $this->value];
    }

    /** @inheritDoc */
    public static function fromArray(array $data): self
    {
        $dto        = new BodyDto();
        $dto->name  = $data['name'];
        $dto->value = $data['value'];

        return $dto;
    }
}
