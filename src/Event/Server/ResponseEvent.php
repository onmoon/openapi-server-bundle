<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Event\Server;

use OnMoon\OpenApiServerBundle\Specification\Definitions\Specification;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * The ResponseEvent event occurs right before the response is
 * sent by the API server
 *
 * This event allows you to modify the Response object before
 * the server will emit it to the client
 */
final class ResponseEvent extends Event
{
    private Response $response;
    private string $operationId;
    private Specification $specification;

    public function __construct(Response $response, string $operationId, Specification $specification)
    {
        $this->response      = $response;
        $this->operationId   = $operationId;
        $this->specification = $specification;
    }

    public function getResponse(): Response
    {
        return $this->response;
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
