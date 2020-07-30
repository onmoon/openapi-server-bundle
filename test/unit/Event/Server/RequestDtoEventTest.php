<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Event\Server;

use OnMoon\OpenApiServerBundle\Event\Server\RequestDtoEvent;
use OnMoon\OpenApiServerBundle\Interfaces\Dto;
use OnMoon\OpenApiServerBundle\Specification\Definitions\Specification;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OnMoon\OpenApiServerBundle\Event\Server\RequestDtoEvent
 */
final class RequestDtoEventTest extends TestCase
{
    public function testRequestDtoEventGettersReturnCorrectValues(): void
    {
        $requestDtoMock    = $this->createMock(Dto::class);
        $operationId       = '12345';
        $specificationMock = $this->createMock(Specification::class);

        $requestDtoEvent = new RequestDtoEvent($requestDtoMock, $operationId, $specificationMock);

        Assert::assertEquals($requestDtoMock, $requestDtoEvent->getRequestDto());
        Assert::assertEquals($operationId, $requestDtoEvent->getOperationId());
        Assert::assertEquals($specificationMock, $requestDtoEvent->getSpecification());
    }
}
