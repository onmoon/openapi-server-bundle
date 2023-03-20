<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Unit\Event\Server;

use cebe\openapi\spec\OpenApi;
use OnMoon\OpenApiServerBundle\Event\Server\RequestDtoEvent;
use OnMoon\OpenApiServerBundle\Interfaces\Dto;
use OnMoon\OpenApiServerBundle\Interfaces\RequestHandler;
use OnMoon\OpenApiServerBundle\Specification\Definitions\Specification;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * @covers \OnMoon\OpenApiServerBundle\Event\Server\RequestDtoEvent
 */
final class RequestDtoEventTest extends TestCase
{
    public function testRequestDtoEventGettersReturnCorrectValues(): void
    {
        $request        = new Request();
        $requestDto     = new class () implements Dto {
            /**
             * @return mixed[]
             */
            public function toArray(): array
            {
                return [];
            }

            /**
             * @param mixed[] $data
             */
            public static function fromArray(array $data): Dto
            {
                return new self();
            }
        };
        $operationId    = '12345';
        $specification  = new Specification([], [], new OpenApi([]));
        $requestHandler = new class () implements RequestHandler{
        };

        $requestDtoEvent = new RequestDtoEvent($requestDto, $operationId, $specification, $requestHandler, $request);

        Assert::assertEquals($requestDto, $requestDtoEvent->getRequestDto());
        Assert::assertEquals($operationId, $requestDtoEvent->getOperationId());
        Assert::assertEquals($specification, $requestDtoEvent->getSpecification());
        Assert::assertEquals($requestHandler, $requestDtoEvent->getRequestHandler());
        Assert::assertEquals($request, $requestDtoEvent->getRequest());
    }

    public function testRequestDtoEventGettersWhenRequestDtoNull(): void
    {
        $request        = new Request();
        $requestDto     = null;
        $operationId    = '12345';
        $specification  = new Specification([], [], new OpenApi([]));
        $requestHandler = new class () implements RequestHandler{
        };

        $requestDtoEvent = new RequestDtoEvent($requestDto, $operationId, $specification, $requestHandler, $request);

        Assert::assertNull($requestDtoEvent->getRequestDto());
        Assert::assertEquals($operationId, $requestDtoEvent->getOperationId());
        Assert::assertEquals($specification, $requestDtoEvent->getSpecification());
        Assert::assertEquals($requestHandler, $requestDtoEvent->getRequestHandler());
        Assert::assertEquals($request, $requestDtoEvent->getRequest());
    }
}
