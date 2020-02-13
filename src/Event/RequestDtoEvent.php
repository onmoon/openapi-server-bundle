<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Event;

use cebe\openapi\spec\Operation;
use OnMoon\OpenApiServerBundle\Interfaces\Dto;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * The RequestDtoEvent event occurs after the Request
 * contents are deserialized in a Dto object representing
 * the API request and before this object is passed to your
 * RequestHandler implementation.
 *
 * This event allows you to modify the RequestDtoEvent contents
 * before it will be passed to your RequestHandler implementation.
 *
 * Note that the ResponseDTO is not created if the API
 * endpoint expects no request body, path or query parameters.
 *
 * @see \Symfony\Component\HttpKernel\Event\RequestEvent
 */
class RequestDtoEvent extends Event
{
    private ?Dto $requestDto;
    private Operation $operation;
    private string $path;
    private string $method;

    public function __construct(
        ?Dto $requestDto,
        Operation $operation,
        string $path,
        string $method
    ) {
        $this->requestDto = $requestDto;
        $this->operation  = $operation;
        $this->path       = $path;
        $this->method     = $method;
    }

    public function requestDto() : ?Dto
    {
        return $this->requestDto;
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
