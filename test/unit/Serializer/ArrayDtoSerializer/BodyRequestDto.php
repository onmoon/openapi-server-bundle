<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Unit\Serializer\ArrayDtoSerializer;

use OnMoon\OpenApiServerBundle\Interfaces\Dto;

class BodyRequestDto implements Dto
{
    private ?string $firstParam = null;
    private ?int $secondParam   = null;

    public function getFirstParam(): ?string
    {
        return $this->firstParam;
    }

    public function getSecondParam(): ?int
    {
        return $this->secondParam;
    }

    /** @inheritDoc */
    public function toArray(): array
    {
        return [
            'firstParam' => $this->firstParam,
            'secondParam' => $this->secondParam,
        ];
    }

    /** @inheritDoc */
    public static function fromArray(array $data): self
    {
        $dto              = new self();
        $dto->firstParam  = $data['firstParam'];
        $dto->secondParam = $data['secondParam'];

        return $dto;
    }
}
