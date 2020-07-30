<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Event\Server;

use OnMoon\OpenApiServerBundle\Event\Server\ResponseDtoEvent;
use OnMoon\OpenApiServerBundle\Interfaces\ResponseDto;
use OnMoon\OpenApiServerBundle\Specification\Definitions\Specification;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OnMoon\OpenApiServerBundle\Event\Server\ResponseDtoEvent
 */
final class ResponseDtoEventTest extends TestCase
{
    public function testResponseDtoGettersReturnCorrectValues(): void
    {
        $responseDtoMock   = $this->createMock(ResponseDto::class);
        $operationId       = '12345';
        $specificationMock = $this->createMock(Specification::class);

        $responseDtoEvent = new ResponseDtoEvent($responseDtoMock, $operationId, $specificationMock);

        Assert::assertEquals($responseDtoMock, $responseDtoEvent->getResponseDto());
        Assert::assertEquals($operationId, $responseDtoEvent->getOperationId());
        Assert::assertEquals($specificationMock, $responseDtoEvent->getSpecification());
    }
}
