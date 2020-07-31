<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Unit\Serializer;

use OnMoon\OpenApiServerBundle\Dto\Example\Request as RequestDto;
use OnMoon\OpenApiServerBundle\Dto\Example\Response as ResponseDto;
use OnMoon\OpenApiServerBundle\Serializer\ArrayDtoSerializer;
use OnMoon\OpenApiServerBundle\Specification\Definitions\ObjectType;
use OnMoon\OpenApiServerBundle\Specification\Definitions\Operation;
use OnMoon\OpenApiServerBundle\Specification\Definitions\Property;
use OnMoon\OpenApiServerBundle\Types\ScalarTypesResolver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Throwable;

class ArrayDtoSerializerTest extends TestCase
{
    /** @var ScalarTypesResolver|MockObject */
    private $resolverMock;

    /** @var Operation|MockObject */
    private $operationMock;

    public function setUp(): void
    {
        parent::setUp();

        $this->resolverMock  = $this->createMock(ScalarTypesResolver::class);
        $this->operationMock = $this->createMock(Operation::class);
    }

    /**
     * @return mixed[]
     */
    public function createRequestDtoProvider(): array
    {
        return [
            ['bodyParameter' => null],
            [
                'bodyParameter' => new ObjectType([new Property('body')]),
            ],
        ];
    }

    /**
     * @param mixed $bodyParameter
     *
     * @throws Throwable
     *
     * @dataProvider createRequestDtoProvider
     */
    public function testCreateRequestDto($bodyParameter): void
    {
        $request = new Request(
            [],
            [],
            [],
            [],
            [],
            [],
            '{}'
        );

        $sendNotRequiredNullableNulls = true;
        $inputDtoFQCN                 = RequestDto::class;

        $requestParameters = [
            'query' => new ObjectType([new Property('query')]),
            'path' => new ObjectType([new Property('path')]),
        ];

        $requestContent = null; // false|null|resource|string

        $resolverConvertResult = null; // bool|mixed|null

        $this->operationMock
            ->expects(self::once())
            ->method('getRequestParameters')
            ->willReturn($requestParameters);

        $this->operationMock
            ->expects(self::once())
            ->method('getRequestBody')
            ->willReturn($bodyParameter);

        $this->resolverMock
            ->method('convert')
            ->willReturn($resolverConvertResult);

        $arrayDtoSerializer = new ArrayDtoSerializer(
            $this->resolverMock,
            $sendNotRequiredNullableNulls
        );

        $arrayDtoSerializer->createRequestDto(
            $request,
            $this->operationMock,
            $inputDtoFQCN
        );
    }

    public function testCreateResponseFromDto(): void
    {
        $sendNotRequiredNullableNulls = true;

        $responseDto = ResponseDto::fromArray(['query' => null]);

        $responseParameter = new ObjectType([new Property('query')]);

        $this->operationMock
            ->expects(self::once())
            ->method('getResponse')
            ->willReturn($responseParameter);

        $arrayDtoSerializer = new ArrayDtoSerializer(
            $this->resolverMock,
            $sendNotRequiredNullableNulls
        );

        $arrayDtoSerializer->createResponseFromDto(
            $responseDto,
            $this->operationMock
        );
    }
}
