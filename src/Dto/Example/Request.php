<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Dto\Example;

use OnMoon\OpenApiServerBundle\Interfaces\Dto;

class Request implements Dto
{
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
}
