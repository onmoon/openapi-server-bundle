<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Apis\Example\PostWithPath\Dto\Request;

use OnMoon\OpenApiServerBundle\Apis\Example\PostWithPath\Dto\Request\Body\BodyDto;
use OnMoon\OpenApiServerBundle\Apis\Example\PostWithPath\Dto\Request\PathParameters\PathParametersDto;
use OnMoon\OpenApiServerBundle\Apis\Example\PostWithPath\Dto\Request\QueryParameters\QueryParametersDto;
use OnMoon\OpenApiServerBundle\Interfaces\Dto;
/**
 * This class was automatically generated
 * You should not change it manually as it will be overwritten
 */

final class PostWithPathRequestDto implements Dto
{
    private PathParametersDto $pathParameters;
    private QueryParametersDto $queryParameters;
    private BodyDto $body;

    public function getPathParameters(): PathParametersDto
    {
        return $this->pathParameters;
    }

    public function getQueryParameters(): QueryParametersDto
    {
        return $this->queryParameters;
    }

    public function getBody(): BodyDto
    {
        return $this->body;
    }

    /** @inheritDoc */
    public function toArray(): array
    {
        return ['pathParameters' => $this->pathParameters->toArray(), 'queryParameters' => $this->queryParameters->toArray(), 'body' => $this->body->toArray()];
    }

    /** @inheritDoc */
    public static function fromArray(array $data): self
    {
        $dto                  = new PostWithPathRequestDto();
        $dto->pathParameters  = PathParametersDto::fromArray($data['pathParameters']);
        $dto->queryParameters = QueryParametersDto::fromArray($data['queryParameters']);
        $dto->body            = BodyDto::fromArray($data['body']);

        return $dto;
    }
}
