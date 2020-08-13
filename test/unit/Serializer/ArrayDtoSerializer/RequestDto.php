<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Unit\Serializer\ArrayDtoSerializer;

use OnMoon\OpenApiServerBundle\Interfaces\Dto;

class RequestDto implements Dto
{
    private PathRequestDto $pathParameters;
    private QueryRequestDto $queryParameters;
    private BodyRequestDto $body;

    public function getPathParameters(): PathRequestDto
    {
        return $this->pathParameters;
    }

    public function getQueryParameters(): QueryRequestDto
    {
        return $this->queryParameters;
    }

    public function getBody(): BodyRequestDto
    {
        return $this->body;
    }

    /** @inheritDoc */
    public function toArray(): array
    {
        return [
            'pathParameters' => $this->pathParameters->toArray(),
            'queryParameters' => $this->queryParameters->toArray(),
            'body' => $this->body->toArray(),
        ];
    }

    /** @inheritDoc */
    public static function fromArray(array $data): self
    {
        $dto                  = new self();
        $dto->pathParameters  = PathRequestDto::fromArray($data['pathParameters']);
        $dto->queryParameters = QueryRequestDto::fromArray($data['queryParameters']);
        $dto->body            = BodyRequestDto::fromArray($data['body']);

        return $dto;
    }
}
