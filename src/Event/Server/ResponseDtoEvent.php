<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Event\Server;

use cebe\openapi\spec\Operation;
use OnMoon\OpenApiServerBundle\Interfaces\ResponseDto;
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
class ResponseDtoEvent extends Event
{
    private ?ResponseDto $responseDto;
    private Operation $operation;
    private string $path;
    private string $method;

    public function __construct(
        ?ResponseDto $responseDto,
        Operation $operation,
        string $path,
        string $method
    ) {
        $this->responseDto = $responseDto;
        $this->operation   = $operation;
        $this->path        = $path;
        $this->method      = $method;
    }

    public function responseDto() : ?ResponseDto
    {
        return $this->responseDto;
    }

    public function operation() : Operation
    {
        return $this->operation;
    }

    public function path() : string
    {
        return $this->path;
    }

    public function method() : string
    {
        return $this->method;
    }
}
