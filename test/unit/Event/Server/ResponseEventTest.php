<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Event\Server;

use OnMoon\OpenApiServerBundle\Event\Server\ResponseEvent;
use OnMoon\OpenApiServerBundle\Specification\Definitions\Specification;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @covers \OnMoon\OpenApiServerBundle\Event\Server\ResponseEvent
 */
final class ResponseEventTest extends TestCase
{
    public function testResponseEventGettersReturnCorrectValues(): void
    {
        $responseMock      = $this->createMock(Response::class);
        $operationId       = '12345';
        $specificationMock = $this->createMock(Specification::class);

        $responseEvent = new ResponseEvent($responseMock, $operationId, $specificationMock);

        Assert::assertEquals($responseMock, $responseEvent->getResponse());
        Assert::assertEquals($operationId, $responseEvent->getOperationId());
        Assert::assertEquals($specificationMock, $responseEvent->getSpecification());
    }
}
