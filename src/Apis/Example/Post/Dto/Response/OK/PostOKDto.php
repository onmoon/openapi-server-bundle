<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Apis\Example\Post\Dto\Response\OK;

use OnMoon\OpenApiServerBundle\Interfaces\ResponseDto;
/**
 * This class was automatically generated
 * You should not change it manually as it will be overwritten
 */

final class PostOKDto implements ResponseDto
{
    /**
     * The operation result.
     */
    private ?string $result = null;

    public function getResult(): ?string
    {
        return $this->result;
    }

    public function setResult(?string $result): self
    {
        $this->result = $result;

        return $this;
    }

    public static function _getResponseCode(): string
    {
        return '200';
    }

    /** @inheritDoc */
    public function toArray(): array
    {
        return ['result' => $this->result];
    }

    /** @inheritDoc */
    public static function fromArray(array $data): self
    {
        $dto         = new PostOKDto();
        $dto->result = $data['result'];

        return $dto;
    }
}
