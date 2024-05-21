<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Unit\Event\Server;

use cebe\openapi\spec\OpenApi;
use OnMoon\OpenApiServerBundle\Event\Server\ResponseDtoEvent;
use OnMoon\OpenApiServerBundle\Interfaces\Dto;
use OnMoon\OpenApiServerBundle\Specification\Definitions\Specification;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

/** @covers \OnMoon\OpenApiServerBundle\Event\Server\ResponseDtoEvent */
final class ResponseDtoEventTest extends TestCase
{
    public function testResponseDtoGettersReturnCorrectValues(): void
    {
        $responseDtoMock = $this->createMock(Dto::class);
        $operationId     = '12345';
        $specification   = new Specification([], [], new OpenApi([]));

        $responseDtoEvent = new ResponseDtoEvent($responseDtoMock, $operationId, $specification);

        Assert::assertEquals($responseDtoMock, $responseDtoEvent->getResponseDto());
        Assert::assertEquals($operationId, $responseDtoEvent->getOperationId());
        Assert::assertEquals($specification, $responseDtoEvent->getSpecification());
    }
}
