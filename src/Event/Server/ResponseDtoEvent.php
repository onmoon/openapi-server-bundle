<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Event\Server;

use OnMoon\OpenApiServerBundle\Interfaces\Dto;
use OnMoon\OpenApiServerBundle\Specification\Definitions\Specification;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * The ResponseDtoEvent event occurs after the request
 * handler class was executed returning a ResponseDto and
 * before this ResponseDto is serialized to a Response.
 *
 * This event allows you to modify the ResponseDto contents
 * before it will be serialized. This can be used as an
 * alternative to modyfing the Response object in a
 * Symfony ResponseEvent, avoiding unnecessary decoding/encoding
 * of the Response body json.
 *
 * Note that the ResponseDTO is not created if the API
 * endpoint has no response body.
 *
 * @see \Symfony\Component\HttpKernel\Event\ResponseEvent
 */
final class ResponseDtoEvent extends Event
{
    private ?Dto $responseDto;
    private string $operationId;
    private Specification $specification;

    public function __construct(?Dto $responseDto, string $operationId, Specification $specification)
    {
        $this->responseDto   = $responseDto;
        $this->operationId   = $operationId;
        $this->specification = $specification;
    }

    public function getResponseDto(): ?Dto
    {
        return $this->responseDto;
    }

    public function getOperationId(): string
    {
        return $this->operationId;
    }

    public function getSpecification(): Specification
    {
        return $this->specification;
    }
}
