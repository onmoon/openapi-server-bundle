<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Unit\Event\Server;

use cebe\openapi\spec\OpenApi;
use OnMoon\OpenApiServerBundle\Event\Server\ResponseEvent;
use OnMoon\OpenApiServerBundle\Interfaces\RequestHandler;
use OnMoon\OpenApiServerBundle\Specification\Definitions\Specification;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @covers \OnMoon\OpenApiServerBundle\Event\Server\ResponseEvent
 */
final class ResponseEventTest extends TestCase
{
    public function testResponseEventGettersReturnCorrectValues(): void
    {
        $response       = new Response();
        $operationId    = '12345';
        $specification  = new Specification([], [], new OpenApi([]));
        $requestHandler = new class () implements RequestHandler{
        };
        $request        = new Request();

        $responseEvent = new ResponseEvent($response, $operationId, $specification, $requestHandler, $request);

        Assert::assertEquals($response, $responseEvent->getResponse());
        Assert::assertEquals($operationId, $responseEvent->getOperationId());
        Assert::assertEquals($specification, $responseEvent->getSpecification());
        Assert::assertEquals($requestHandler, $responseEvent->getRequestHandler());
        Assert::assertEquals($request, $responseEvent->getRequest());
    }
}
