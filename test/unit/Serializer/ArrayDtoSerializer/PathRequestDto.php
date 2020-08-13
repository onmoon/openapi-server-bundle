<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Unit\Serializer\ArrayDtoSerializer;

use OnMoon\OpenApiServerBundle\Interfaces\Dto;

class PathRequestDto implements Dto
{
    private ?string $firstParam = null;

    public function getFirstParam(): ?string
    {
        return $this->firstParam;
    }

    /** @inheritDoc */
    public function toArray(): array
    {
        return [
            'firstParam' => $this->firstParam,
        ];
    }

    /** @inheritDoc */
    public static function fromArray(array $data): self
    {
        $dto             = new self();
        $dto->firstParam = $data['firstParam'];

        return $dto;
    }
}
