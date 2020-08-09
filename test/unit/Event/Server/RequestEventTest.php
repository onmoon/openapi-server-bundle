<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Unit\Event\Server;

use cebe\openapi\spec\OpenApi;
use OnMoon\OpenApiServerBundle\Event\Server\RequestEvent;
use OnMoon\OpenApiServerBundle\Specification\Definitions\Specification;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * @covers \OnMoon\OpenApiServerBundle\Event\Server\RequestEvent
 */
final class RequestEventTest extends TestCase
{
    public function testRequestEventGettersReturnCorrectValues(): void
    {
        $requestMock   = new Request();
        $operationId   = '12345';
        $specification = new Specification([], new OpenApi([]));

        $requestDtoEvent = new RequestEvent($requestMock, $operationId, $specification);

        Assert::assertEquals($requestMock, $requestDtoEvent->getRequest());
        Assert::assertEquals($operationId, $requestDtoEvent->getOperationId());
        Assert::assertEquals($specification, $requestDtoEvent->getSpecification());
    }
}
