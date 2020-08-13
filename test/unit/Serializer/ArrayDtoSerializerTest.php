<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Unit\Serializer;

use OnMoon\OpenApiServerBundle\Interfaces\Dto;
use OnMoon\OpenApiServerBundle\Interfaces\ResponseDto;
use OnMoon\OpenApiServerBundle\Serializer\ArrayDtoSerializer;
use OnMoon\OpenApiServerBundle\Specification\Definitions\ObjectType;
use OnMoon\OpenApiServerBundle\Specification\Definitions\Operation;
use OnMoon\OpenApiServerBundle\Specification\Definitions\Property;
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

        /** @var Dto|mixed $requestDto */
        $requestDto = $arrayDtoSerializer->createRequestDto(
            $request,
            $operation,
            $this->makeRequestDtoFCQN()
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

    private function makeRequestDtoFCQN(): Dto
    {
        return new class () implements Dto
        {
            private Dto $pathParameters;
            private Dto $queryParameters;
            private Dto $body;

            public function getPathParameters(): Dto
            {
                return $this->pathParameters;
            }

            public function getQueryParameters(): Dto
            {
                return $this->queryParameters;
            }

            public function getBody(): Dto
            {
                return $this->body;
            }

            /** @inheritDoc */
            public function toArray(): array
            {
                return [
                    'pathParameters' => $this->pathParameters->toArray(),
                    'queryParameters' => $this->queryParameters->toArray(),
                    'body' => $this->body->toArray(),
                ];
            }

            /** @inheritDoc */
            public static function fromArray(array $data): self
            {
                $pathDto = new class () implements Dto
                {
                    private ?string $firstParam = null;

                    public function getFirstParam(): ?string
                    {
                        return $this->firstParam;
                    }

                    /** @inheritDoc */
                    public function toArray(): array
                    {
                        return [
                            'firstParam' => $this->firstParam,
                        ];
                    }

                    /** @inheritDoc */
                    public static function fromArray(array $data): self
                    {
                        $dto             = new self();
                        $dto->firstParam = $data['firstParam'];

                        return $dto;
                    }
                };

                $queryDto = new class () implements Dto
                {
                    private ?string $firstParam = null;

                    public function getFirstParam(): ?string
                    {
                        return $this->firstParam;
                    }

                    /** @inheritDoc */
                    public function toArray(): array
                    {
                        return [
                            'firstParam' => $this->firstParam,
                        ];
                    }

                    /** @inheritDoc */
                    public static function fromArray(array $data): self
                    {
                        $dto             = new self();
                        $dto->firstParam = $data['firstParam'];

                        return $dto;
                    }
                };

                $bodyDto = new class () implements Dto
                {
                    private ?string $firstParam = null;
                    private ?int $secondParam   = null;

                    public function getFirstParam(): ?string
                    {
                        return $this->firstParam;
                    }

                    public function getSecondParam(): ?int
                    {
                        return $this->secondParam;
                    }

                    /** @inheritDoc */
                    public function toArray(): array
                    {
                        return [
                            'firstParam' => $this->firstParam,
                            'secondParam' => $this->secondParam,
                        ];
                    }

                    /** @inheritDoc */
                    public static function fromArray(array $data): self
                    {
                        $dto              = new self();
                        $dto->firstParam  = $data['firstParam'];
                        $dto->secondParam = $data['secondParam'];

                        return $dto;
                    }
                };

                $dto                  = new self();
                $dto->pathParameters  = $pathDto::fromArray($data['pathParameters']);
                $dto->queryParameters = $queryDto::fromArray($data['queryParameters']);
                $dto->body            = $bodyDto::fromArray($data['body']);

                return $dto;
            }
        };
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
        $okResponseDtoFQCN = $this->makeOKResponseDtoFCQN();

        /** @var ResponseDto $okResponseDto */
        $okResponseDto = $okResponseDtoFQCN::fromArray([
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
                    $okResponseDtoFQCN::_getResponseCode() => new ObjectType([
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
        $okResponseDtoFQCN = $this->makeOKResponseDtoFCQN();

        /** @var ResponseDto $okResponseDto */
        $okResponseDto = $okResponseDtoFQCN::fromArray($properties);

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
                    $okResponseDtoFQCN::_getResponseCode() => new ObjectType([
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

    private function makeOKResponseDtoFCQN(): ResponseDto
    {
        return new class () implements ResponseDto {
            private ?string $firstProp  = null;
            private ?string $secondProp = null;

            public function getFirstProp(): ?string
            {
                return $this->firstProp;
            }

            public function setFirstProp(?string $firstProp): self
            {
                $this->firstProp = $firstProp;

                return $this;
            }

            public function getSecondProp(): ?string
            {
                return $this->secondProp;
            }

            public function setSecondProp(?string $secondProp): self
            {
                $this->secondProp = $secondProp;

                return $this;
            }

            public static function _getResponseCode(): string
            {
                return '200';
            }

            /** @inheritDoc */
            public function toArray(): array
            {
                return [
                    'firstProp' => $this->firstProp,
                    'secondProp' => $this->secondProp,
                ];
            }

            /** @inheritDoc */
            public static function fromArray(array $data): self
            {
                $dto             = new self();
                $dto->firstProp  = $data['firstProp'];
                $dto->secondProp = $data['secondProp'];

                return $dto;
            }
        };
    }
}
