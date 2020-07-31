<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Dto\Example;

use OnMoon\OpenApiServerBundle\Interfaces\ResponseDto;

class Response implements ResponseDto
{
    public const RESPONSE_CODE = '200';

    /** @var mixed[] */
    private array $data = [];

    /** @inheritDoc */
    public function toArray(): array
    {
        return $this->data;
    }

    /** @inheritDoc */
    public static function fromArray(array $data): self
    {
        $instance       = new self();
        $instance->data = $data;

        return $instance;
    }

    public static function _getResponseCode(): string
    {
        return self::RESPONSE_CODE;
    }
}
