<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Event\Server;

use OnMoon\OpenApiServerBundle\Specification\Definitions\Specification;
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
    private string $operationId;
    private Specification $specification;

    public function __construct(Request $request, string $operationId, Specification $specification)
    {
        $this->request       = $request;
        $this->operationId   = $operationId;
        $this->specification = $specification;
    }

    public function getRequest(): Request
    {
        return $this->request;
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
