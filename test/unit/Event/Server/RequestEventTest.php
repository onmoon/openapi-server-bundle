<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Event\Server;

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
        $requestMock       = $this->createMock(Request::class);
        $operationId       = '12345';
        $specificationMock = $this->createMock(Specification::class);

        $requestDtoEvent = new RequestEvent($requestMock, $operationId, $specificationMock);

        Assert::assertEquals($requestMock, $requestDtoEvent->getRequest());
        Assert::assertEquals($operationId, $requestDtoEvent->getOperationId());
        Assert::assertEquals($specificationMock, $requestDtoEvent->getSpecification());
    }
}
