<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Event;

use cebe\openapi\spec\Operation;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * The ResponseEvent event occurs right before the response is
 * sent by the API server
 *
 * This event allows you to modify the Response object before
 * the server will emit it to the client
 */
class ResponseEvent extends Event
{
    private Response $response;
    private Operation $operation;
    private string $path;
    private string $method;

    public function __construct(
        Response $response,
        Operation $operation,
        string $path,
        string $method
    ) {
        $this->response  = $response;
        $this->operation = $operation;
        $this->path      = $path;
        $this->method    = $method;
    }

    public function response() : Response
    {
        return $this->response;
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
