<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Event\Server;

use OnMoon\OpenApiServerBundle\Interfaces\Dto;
use OnMoon\OpenApiServerBundle\Interfaces\RequestHandler;
use OnMoon\OpenApiServerBundle\Specification\Definitions\Specification;
use Symfony\Component\HttpFoundation\Request;
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
final class RequestDtoEvent extends Event
{
    private ?Dto $requestDto;
    private string $operationId;
    private Specification $specification;
    private RequestHandler $requestHandler;
    private Request $request;

    public function __construct(?Dto $requestDto, string $operationId, Specification $specification, RequestHandler $requestHandler, Request $request)
    {
        $this->requestDto     = $requestDto;
        $this->operationId    = $operationId;
        $this->specification  = $specification;
        $this->requestHandler = $requestHandler;
        $this->request        = $request;
    }

    public function getRequestDto(): ?Dto
    {
        return $this->requestDto;
    }

    public function getOperationId(): string
    {
        return $this->operationId;
    }

    public function getSpecification(): Specification
    {
        return $this->specification;
    }

    public function getRequestHandler(): RequestHandler
    {
        return $this->requestHandler;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }
}
