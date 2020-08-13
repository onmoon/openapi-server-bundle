<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Unit\Serializer;

use OnMoon\OpenApiServerBundle\Serializer\ArrayDtoSerializer;
use OnMoon\OpenApiServerBundle\Specification\Definitions\ObjectType;
use OnMoon\OpenApiServerBundle\Specification\Definitions\Operation;
use OnMoon\OpenApiServerBundle\Specification\Definitions\Property;
use OnMoon\OpenApiServerBundle\Test\Unit\Serializer\ArrayDtoSerializer\RequestDto;
use OnMoon\OpenApiServerBundle\Test\Unit\Serializer\ArrayDtoSerializer\ResponseDto;
use OnMoon\OpenApiServerBundle\Types\ScalarTypesResolver;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Throwable;

use function Safe\json_encode;

/**
 * @covers \OnMoon\OpenApiServerBundle\Serializer\ArrayDtoSerializer
 */
class ArrayDtoSerializerTest extends TestCase
{
    private const OK_RESPONSE_DTO_FIRST_PROP  = 'firstProp';
    private const OK_RESPONSE_DTO_SECOND_PROP = 'secondProp';

    /**
     * @return mixed[]
     */
    public function createRequestDtoWithPathProvider(): array
    {
        return [
            [
                'payload' => [
                    'queryParams' => [],
                    'pathParams' => [],
                    'bodyParams' => [],
                ],
                'expected' => [
                    'queryParams' => ['firstParam' => 'SomeDefaultQueryParam'],
                    'pathParams' => ['firstParam' => 'SomeDefaultPathParam'],
                    'bodyParams' => [
                        'firstParam' => 'SomeDefaultFirstBodyParam',
                        'secondParam' => 0,
                    ],
                ],
            ],
            [
                'payload' => [
                    'queryParams' => [],
                    'pathParams' => 'BadPathParams',
                    'bodyParams' => [],
                ],
                'expected' => [
                    'queryParams' => ['firstParam' => 'SomeDefaultQueryParam'],
                    'pathParams' => ['firstParam' => 'SomeDefaultPathParam'],
                    'bodyParams' => [
                        'firstParam' => 'SomeDefaultFirstBodyParam',
                        'secondParam' => 0,
                    ],
                ],
            ],
            [
                'payload' => [
                    'queryParams' => ['firstParam' => 'SomeCustomFirstQueryParam'],
                    'pathParams' => ['firstParam' => 'SomeCustomFirstPathParam'],
                    'bodyParams' => [
                        'firstParam' => 'SomeCustomFirstBodyParam',
                        'secondParam' => 1000,
                    ],
                ],
                'expected' => [
                    'queryParams' => ['firstParam' => 'SomeCustomFirstQueryParam'],
                    'pathParams' => ['firstParam' => 'SomeCustomFirstPathParam'],
                    'bodyParams' => [
                        'firstParam' => 'SomeCustomFirstBodyParam',
                        'secondParam' => 1000,
                    ],
                ],
            ],
            [
                'payload' => [
                    'queryParams' => [],
                    'pathParams' => [],
                    'bodyParams' => ['secondParam' => 1000],
                ],
                'expected' => [
                    'queryParams' => ['firstParam' => 'SomeDefaultQueryParam'],
                    'pathParams' => ['firstParam' => 'SomeDefaultPathParam'],
                    'bodyParams' => [
                        'firstParam' => 'SomeDefaultFirstBodyParam',
                        'secondParam' => 1000,
                    ],
                ],
            ],
        ];
    }

    /**
     * @param mixed[] $payload
     * @param mixed[] $expected
     *
     * @throws Throwable
     *
     * @dataProvider createRequestDtoWithPathProvider
     */
    public function testCreateRequestDtoWithPath(array $payload, array $expected): void
    {
        $request = new Request(
            $payload['queryParams'],
            [],
            ['_route_params' => $payload['pathParams']],
            [],
            [],
            [],
            json_encode($payload['bodyParams'])
        );

        $requestQuery = new ObjectType([
            (new Property('firstParam'))
                ->setDefaultValue('SomeDefaultQueryParam'),
        ]);
        $requestPath  = new ObjectType([
            (new Property('firstParam'))
                ->setDefaultValue('SomeDefaultPathParam'),
        ]);
        $requestBody  = new ObjectType([
            (new Property('firstParam'))
                ->setDefaultValue('SomeDefaultFirstBodyParam'),
            (new Property('secondParam'))
                ->setDefaultValue(0)
                ->setScalarTypeId(10),
        ]);

        $operation = new Operation(
            '/example/path',
            'POST',
            'PostRequest',
            null,
            $requestBody,
            [
                'query' => $requestQuery,
                'path' => $requestPath,
            ],
            []
        );

        $arrayDtoSerializer = new ArrayDtoSerializer(
            new ScalarTypesResolver(),
            true
        );

        /** @var RequestDto $requestDto */
        $requestDto = $arrayDtoSerializer->createRequestDto(
            $request,
            $operation,
            RequestDto::class
        );

        Assert::assertSame(
            $expected['queryParams']['firstParam'],
            $requestDto->getQueryParameters()->getFirstParam()
        );
        Assert::assertSame(
            $expected['pathParams']['firstParam'],
            $requestDto->getPathParameters()->getFirstParam()
        );
        Assert::assertSame(
            $expected['bodyParams']['firstParam'],
            $requestDto->getBody()->getFirstParam()
        );
        Assert::assertSame(
            $expected['bodyParams']['secondParam'],
            $requestDto->getBody()->getSecondParam()
        );
    }

    /**
     * @return mixed[]
     */
    public function createResponseFromDtoPropertyIsNullProvider(): array
    {
        return [
            [
                'propertyConditions' => [
                    'defaultValue' => null,
                    'isRequired' => false,
                    'isNullable' => false,
                ],
                'sendNotRequiredNullableNulls' => false,
                'result' => [],
            ],
            [
                'propertyConditions' => [
                    'defaultValue' => 'SomeFirstDefaultNotNullValue',
                    'isRequired' => false,
                    'isNullable' => false,
                ],
                'sendNotRequiredNullableNulls' => false,
                'result' => [self::OK_RESPONSE_DTO_FIRST_PROP => 'SomeFirstDefaultNotNullValue'],
            ],
            [
                'propertyConditions' => [
                    'defaultValue' => null,
                    'isRequired' => true,
                    'isNullable' => false,
                ],
                'sendNotRequiredNullableNulls' => false,
                'result' => [self::OK_RESPONSE_DTO_FIRST_PROP => null],
            ],
            [
                'propertyConditions' => [
                    'defaultValue' => null,
                    'isRequired' => false,
                    'isNullable' => true,
                ],
                'sendNotRequiredNullableNulls' => false,
                'result' => [],
            ],
            [
                'propertyConditions' => [
                    'defaultValue' => null,
                    'isRequired' => false,
                    'isNullable' => true,
                ],
                'sendNotRequiredNullableNulls' => true,
                'result' => [self::OK_RESPONSE_DTO_FIRST_PROP => null],
            ],
            [
                'propertyConditions' => [
                    'defaultValue' => null,
                    'isRequired' => false,
                    'isNullable' => false,
                ],
                'sendNotRequiredNullableNulls' => true,
                'result' => [],
            ],
        ];
    }

    /**
     * @param mixed[] $propertyConditions
     * @param mixed[] $expectedResult
     *
     * @dataProvider createResponseFromDtoPropertyIsNullProvider
     */
    public function testCreateResponseFromDtoPropertyIsNull(
        array $propertyConditions,
        bool $sendNotRequiredNullableNulls,
        array $expectedResult
    ): void {
        $okResponseDto = ResponseDto::fromArray([
            self::OK_RESPONSE_DTO_FIRST_PROP => null,
            self::OK_RESPONSE_DTO_SECOND_PROP => null,
        ]);

        $arrayDtoSerializer = new ArrayDtoSerializer(new ScalarTypesResolver(), $sendNotRequiredNullableNulls);

        $result = $arrayDtoSerializer->createResponseFromDto(
            $okResponseDto,
            new Operation(
                '/example/path',
                'POST',
                'PostRequestHandler',
                null,
                null,
                [],
                [
                    ResponseDto::_getResponseCode() => new ObjectType([
                        (new Property(self::OK_RESPONSE_DTO_FIRST_PROP))
                            ->setDefaultValue($propertyConditions['defaultValue'])
                            ->setRequired($propertyConditions['isRequired'])
                            ->setNullable($propertyConditions['isNullable']),
                    ]),
                ]
            )
        );

        Assert::assertSame($expectedResult, $result);
    }

    /**
     * @return mixed[]
     */
    public function createResponseFromDtoMultiplePropertiesProvider(): array
    {
        return [
            [
                'properties' => [
                    self::OK_RESPONSE_DTO_FIRST_PROP => null,
                    self::OK_RESPONSE_DTO_SECOND_PROP => null,
                ],
                'expected' => [
                    self::OK_RESPONSE_DTO_FIRST_PROP => 'SomeFirstDefaultValue',
                    self::OK_RESPONSE_DTO_SECOND_PROP => 'SomeSecondDefaultValue',
                ],
            ],
            [
                'properties' => [
                    self::OK_RESPONSE_DTO_FIRST_PROP => 'SomeFirstNotNullValue',
                    self::OK_RESPONSE_DTO_SECOND_PROP => 'SomeSecondNotNullValue',
                ],
                'expected' => [
                    self::OK_RESPONSE_DTO_FIRST_PROP => 'SomeFirstNotNullValue',
                    self::OK_RESPONSE_DTO_SECOND_PROP => 'SomeSecondNotNullValue',
                ],
            ],
        ];
    }

    /**
     * @param mixed[] $properties
     * @param mixed[] $expected
     *
     * @dataProvider createResponseFromDtoMultiplePropertiesProvider
     */
    public function testCreateResponseFromDtoMultipleProperties(array $properties, array $expected): void
    {
        $okResponseDto = ResponseDto::fromArray($properties);

        $arrayDtoSerializer = new ArrayDtoSerializer(new ScalarTypesResolver(), false);

        $result = $arrayDtoSerializer->createResponseFromDto(
            $okResponseDto,
            new Operation(
                '/example/path',
                'POST',
                'PostRequestHandler',
                null,
                null,
                [],
                [
                    ResponseDto::_getResponseCode() => new ObjectType([
                        (new Property(self::OK_RESPONSE_DTO_FIRST_PROP))
                            ->setDefaultValue('SomeFirstDefaultValue'),
                        (new Property(self::OK_RESPONSE_DTO_SECOND_PROP))
                            ->setDefaultValue('SomeSecondDefaultValue'),
                    ]),
                ]
            )
        );

        Assert::assertSame($expected, $result);
    }
}
