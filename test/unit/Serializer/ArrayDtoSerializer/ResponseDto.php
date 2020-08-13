<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Unit\Serializer\ArrayDtoSerializer;

use OnMoon\OpenApiServerBundle\Interfaces\ResponseDto as ResponseDtoInterface;

class ResponseDto implements ResponseDtoInterface
{
    private ?string $firstProp  = null;
    private ?string $secondProp = null;

    public function getFirstProp(): ?string
    {
        return $this->firstProp;
    }

    public function setFirstProp(?string $firstProp): self
    {
        $this->firstProp = $firstProp;

        return $this;
    }

    public function getSecondProp(): ?string
    {
        return $this->secondProp;
    }

    public function setSecondProp(?string $secondProp): self
    {
        $this->secondProp = $secondProp;

        return $this;
    }

    public static function _getResponseCode(): string
    {
        return '200';
    }

    /** @inheritDoc */
    public function toArray(): array
    {
        return [
            'firstProp' => $this->firstProp,
            'secondProp' => $this->secondProp,
        ];
    }

    /** @inheritDoc */
    public static function fromArray(array $data): self
    {
        $dto             = new self();
        $dto->firstProp  = $data['firstProp'];
        $dto->secondProp = $data['secondProp'];

        return $dto;
    }
}
