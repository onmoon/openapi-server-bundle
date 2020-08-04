<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Apis\Example\Post\Dto\Request\QueryParameters;

use OnMoon\OpenApiServerBundle\Interfaces\Dto;
/**
 * This class was automatically generated
 * You should not change it manually as it will be overwritten
 */

final class QueryParametersDto implements Dto
{
    /**
     * Query Param
     */
    private ?string $queryParam = null;

    public function getQueryParam(): ?string
    {
        return $this->queryParam;
    }

    /** @inheritDoc */
    public function toArray(): array
    {
        return ['queryParam' => $this->queryParam];
    }

    /** @inheritDoc */
    public static function fromArray(array $data): self
    {
        $dto             = new QueryParametersDto();
        $dto->queryParam = $data['queryParam'];

        return $dto;
    }
}
