<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Apis\Example\PostWithPath\Dto\Request\PathParameters;

use OnMoon\OpenApiServerBundle\Interfaces\Dto;
/**
 * This class was automatically generated
 * You should not change it manually as it will be overwritten
 */

final class PathParametersDto implements Dto
{
    /**
     * Path Param
     */
    private string $pathParam;

    public function getPathParam(): string
    {
        return $this->pathParam;
    }

    /** @inheritDoc */
    public function toArray(): array
    {
        return ['pathParam' => $this->pathParam];
    }

    /** @inheritDoc */
    public static function fromArray(array $data): self
    {
        $dto            = new PathParametersDto();
        $dto->pathParam = $data['pathParam'];

        return $dto;
    }
}
