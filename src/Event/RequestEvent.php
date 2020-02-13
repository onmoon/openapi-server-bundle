<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Event;

use cebe\openapi\spec\Operation;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * The RequestEvent event occurs right before the request is
 * validated against the OpenAPI Schema
 *
 * This event allows you to modify the Operation and Request
 * objects prior to performing the validation and processing
 * the request
 */
class RequestEvent extends Event
{
    private Request $request;
    private Operation $operation;
    private string $path;
    private string $method;

    public function __construct(
        Request $request,
        Operation $operation,
        string $path,
        string $method
    ) {
        $this->request   = $request;
        $this->operation = $operation;
        $this->path      = $path;
        $this->method    = $method;
    }

    public function request() : Request
    {
        return $this->request;
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
