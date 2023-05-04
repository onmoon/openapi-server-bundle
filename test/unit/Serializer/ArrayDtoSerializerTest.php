<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Unit\Serializer;

use OnMoon\OpenApiServerBundle\Interfaces\Dto;
use OnMoon\OpenApiServerBundle\Serializer\ArrayDtoSerializer;
use OnMoon\OpenApiServerBundle\Specification\Definitions\ObjectSchema;
use OnMoon\OpenApiServerBundle\Specification\Definitions\Operation;
use OnMoon\OpenApiServerBundle\Specification\Definitions\Property;
use OnMoon\OpenApiServerBundle\Types\ScalarTypesResolver;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Throwable;

use function get_class;
use function Safe\fopen;
use function Safe\fwrite;
use function Safe\json_encode;
use function Safe\rewind;

/**
 * @covers \OnMoon\OpenApiServerBundle\Serializer\ArrayDtoSerializer
 */
class ArrayDtoSerializerTest extends TestCase
{
    private const OK_RESPONSE_DTO_FIRST_PROP  = 'firstProp';
    private const OK_RESPONSE_DTO_SECOND_PROP = 'secondProp';
    private const OK_RESPONSE_DTO_THIRD_PROP  = 'thirdProp';

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
                        'thirdParam' => null,
                    ],
                ],
                'conditions' => ['bodyIsResource' => false],
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
                        'thirdParam' => null,
                    ],
                ],
                'conditions' => ['bodyIsResource' => false],
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
                        'thirdParam' => null,
                    ],
                ],
                'conditions' => ['bodyIsResource' => false],
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
                        'thirdParam' => null,
                    ],
                ],
                'conditions' => ['bodyIsResource' => false],
            ],
            [
                'payload' => [
                    'queryParams' => [],
                    'pathParams' => [],
                    'bodyParams' => [
                        'thirdParam' => [],
                    ],
                ],
                'expected' => [
                    'queryParams' => ['firstParam' => 'SomeDefaultQueryParam'],
                    'pathParams' => ['firstParam' => 'SomeDefaultPathParam'],
                    'bodyParams' => [
                        'firstParam' => 'SomeDefaultFirstBodyParam',
                        'secondParam' => 0,
                        'thirdParam' => ['firstParam' => 'SomeDefaultFirstBodySubParam'],
                    ],
                ],
                'conditions' => ['bodyIsResource' => false],
            ],
            [
                'payload' => [
                    'queryParams' => [],
                    'pathParams' => [],
                    'bodyParams' => [
                        'thirdParam' => ['firstParam' => 'SomeCustomFirstBodySubParam'],
                    ],
                ],
                'expected' => [
                    'queryParams' => ['firstParam' => 'SomeDefaultQueryParam'],
                    'pathParams' => ['firstParam' => 'SomeDefaultPathParam'],
                    'bodyParams' => [
                        'firstParam' => 'SomeDefaultFirstBodyParam',
                        'secondParam' => 0,
                        'thirdParam' => ['firstParam' => 'SomeCustomFirstBodySubParam'],
                    ],
                ],
                'conditions' => ['bodyIsResource' => false],
            ],
            [
                'payload' => [
                    'queryParams' => [],
                    'pathParams' => [],
                    'bodyParams' => [
                        'firstParam' => 'SomeCustomFirstBodyParam',
                        'secondParam' => 1000,
                        'thirdParam' => ['firstParam' => 'SomeCustomFirstBodySubParam'],
                    ],
                ],
                'expected' => [
                    'queryParams' => ['firstParam' => 'SomeDefaultQueryParam'],
                    'pathParams' => ['firstParam' => 'SomeDefaultPathParam'],
                    'bodyParams' => [
                        'firstParam' => 'SomeCustomFirstBodyParam',
                        'secondParam' => 1000,
                        'thirdParam' => ['firstParam' => 'SomeCustomFirstBodySubParam'],
                    ],
                ],
                'conditions' => ['bodyIsResource' => true],
            ],
        ];
    }

    /**
     * @param mixed[] $payload
     * @param mixed[] $expected
     * @param mixed[] $conditions
     *
     * @throws Throwable
     *
     * @dataProvider createRequestDtoWithPathProvider
     */
    public function testCreateRequestDtoWithPath(array $payload, array $expected, array $conditions): void
    {
        $requestContent = json_encode($payload['bodyParams']);
        if ($conditions['bodyIsResource'] === true) {
            $requestContentResource = fopen('php://temp', 'rb+');
            fwrite($requestContentResource, $requestContent);
            rewind($requestContentResource);
            $requestContent = $requestContentResource;
        }

        $request = new Request(
            $payload['queryParams'],
            [],
            ['_route_params' => $payload['pathParams']],
            [],
            [],
            [],
            $requestContent
        );

        $requestQuery = new ObjectSchema([
            (new Property('firstParam'))
                ->setDefaultValue('SomeDefaultQueryParam'),
        ]);
        $requestPath  = new ObjectSchema([
            (new Property('firstParam'))
                ->setDefaultValue('SomeDefaultPathParam'),
        ]);

        $requestBodyThirdParam = new Property('thirdParam');
        if (isset($payload['bodyParams']['thirdParam'])) {
            $requestBodyThirdParam->setObjectTypeDefinition(
                new ObjectSchema([
                    (new Property('firstParam'))
                        ->setDefaultValue('SomeDefaultFirstBodySubParam'),
                ])
            );
        }

        $requestBody = new ObjectSchema([
            (new Property('firstParam'))
                ->setDefaultValue('SomeDefaultFirstBodyParam'),
            (new Property('secondParam'))
                ->setDefaultValue(0)
                ->setScalarTypeId(10),
            $requestBodyThirdParam,
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
            get_class($this->makeRequestDtoFCQN())
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

        if (isset($expected['bodyParams']['thirdParam'])) {
            Assert::assertSame(
                $expected['bodyParams']['thirdParam']['firstParam'],
                $requestDto->getBody()->getThirdParam()->getFirstParam()
            );
        } else {
            Assert::assertNull($requestDto->getBody()->getThirdParam());
        }
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
                    private ?Dto $thirdParam    = null;

                    public function getFirstParam(): ?string
                    {
                        return $this->firstParam;
                    }

                    public function getSecondParam(): ?int
                    {
                        return $this->secondParam;
                    }

                    public function getThirdParam(): ?Dto
                    {
                        return $this->thirdParam;
                    }

                    /** @inheritDoc */
                    public function toArray(): array
                    {
                        return [
                            'firstParam' => $this->firstParam,
                            'secondParam' => $this->secondParam,
                            'thirdParam' => $this->thirdParam !== null
                                ? $this->thirdParam->toArray()
                                : null,
                        ];
                    }

                    /** @inheritDoc */
                    public static function fromArray(array $data): self
                    {
                        $thirdParamDto = new class () implements Dto
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

                        $dto              = new self();
                        $dto->firstParam  = $data['firstParam'];
                        $dto->secondParam = $data['secondParam'];
                        $dto->thirdParam  = isset($data['thirdParam'])
                            ? $thirdParamDto::fromArray($data['thirdParam'])
                            : null;

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
        /** @var Dto $okResponseDtoFQCN */
        $okResponseDtoFQCN = get_class($this->makeOKResponseDtoFCQN());

        $okResponseDto = $okResponseDtoFQCN::fromArray([
            self::OK_RESPONSE_DTO_FIRST_PROP => null,
            self::OK_RESPONSE_DTO_SECOND_PROP => null,
            self::OK_RESPONSE_DTO_THIRD_PROP => null,
        ]);

        $arrayDtoSerializer = new ArrayDtoSerializer(new ScalarTypesResolver(), $sendNotRequiredNullableNulls);

        $result = $arrayDtoSerializer->createResponseFromDto(
            $okResponseDto,
            new ObjectSchema([
                (new Property(self::OK_RESPONSE_DTO_FIRST_PROP))
                    ->setDefaultValue($propertyConditions['defaultValue'])
                    ->setRequired($propertyConditions['isRequired'])
                    ->setNullable($propertyConditions['isNullable']),
            ])
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
                    self::OK_RESPONSE_DTO_THIRD_PROP => null,
                ],
                'expected' => [
                    self::OK_RESPONSE_DTO_FIRST_PROP => 'SomeFirstDefaultValue',
                    self::OK_RESPONSE_DTO_SECOND_PROP => 'SomeSecondDefaultValue',
                    self::OK_RESPONSE_DTO_THIRD_PROP => null,
                ],
            ],
            [
                'properties' => [
                    self::OK_RESPONSE_DTO_FIRST_PROP => 'SomeFirstNotNullValue',
                    self::OK_RESPONSE_DTO_SECOND_PROP => 'SomeSecondNotNullValue',
                    self::OK_RESPONSE_DTO_THIRD_PROP => ['firstParam' => null],
                ],
                'expected' => [
                    self::OK_RESPONSE_DTO_FIRST_PROP => 'SomeFirstNotNullValue',
                    self::OK_RESPONSE_DTO_SECOND_PROP => 'SomeSecondNotNullValue',
                    self::OK_RESPONSE_DTO_THIRD_PROP => ['firstParam' => 'SomeDefaultFirstSubParam'],
                ],
            ],
            [
                'properties' => [
                    self::OK_RESPONSE_DTO_FIRST_PROP => 'SomeFirstNotNullValue',
                    self::OK_RESPONSE_DTO_SECOND_PROP => 'SomeSecondNotNullValue',
                    self::OK_RESPONSE_DTO_THIRD_PROP => ['firstParam' => 'SomeCustomFirstSubParam'],
                ],
                'expected' => [
                    self::OK_RESPONSE_DTO_FIRST_PROP => 'SomeFirstNotNullValue',
                    self::OK_RESPONSE_DTO_SECOND_PROP => 'SomeSecondNotNullValue',
                    self::OK_RESPONSE_DTO_THIRD_PROP => ['firstParam' => 'SomeCustomFirstSubParam'],
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
        /** @var Dto $okResponseDtoFQCN */
        $okResponseDtoFQCN = get_class($this->makeOKResponseDtoFCQN());

        $okResponseDto = $okResponseDtoFQCN::fromArray($properties);

        $arrayDtoSerializer = new ArrayDtoSerializer(new ScalarTypesResolver(), false);

        $responseThirdParam = new Property(self::OK_RESPONSE_DTO_THIRD_PROP);
        if (isset($properties[self::OK_RESPONSE_DTO_THIRD_PROP])) {
            $responseThirdParam->setObjectTypeDefinition(
                new ObjectSchema([
                    (new Property('firstParam'))
                        ->setDefaultValue('SomeDefaultFirstSubParam'),
                ])
            );
        }

        $result = $arrayDtoSerializer->createResponseFromDto(
            $okResponseDto,
            new ObjectSchema([
                (new Property(self::OK_RESPONSE_DTO_FIRST_PROP))
                    ->setDefaultValue('SomeFirstDefaultValue'),
                (new Property(self::OK_RESPONSE_DTO_SECOND_PROP))
                    ->setDefaultValue('SomeSecondDefaultValue'),
                $responseThirdParam,
            ])
        );

        Assert::assertSame(
            $expected[self::OK_RESPONSE_DTO_FIRST_PROP],
            $result[self::OK_RESPONSE_DTO_FIRST_PROP]
        );
        Assert::assertSame(
            $expected[self::OK_RESPONSE_DTO_SECOND_PROP],
            $result[self::OK_RESPONSE_DTO_SECOND_PROP]
        );

        if (! isset($expected[self::OK_RESPONSE_DTO_THIRD_PROP])) {
            return;
        }

        Assert::assertSame(
            $expected[self::OK_RESPONSE_DTO_THIRD_PROP]['firstParam'],
            $result[self::OK_RESPONSE_DTO_THIRD_PROP]['firstParam']
        );
    }

    private function makeOKResponseDtoFCQN(): Dto
    {
        return new class () implements Dto {
            private ?string $firstProp  = null;
            private ?string $secondProp = null;
            private ?Dto $thirdProp     = null;

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

            public function getThirdProp(): ?Dto
            {
                return $this->thirdProp;
            }

            public function setThirdProp(?Dto $thirdProp): self
            {
                $this->thirdProp = $thirdProp;

                return $this;
            }

            // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
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
                    'thirdProp' => $this->thirdProp !== null
                        ? $this->thirdProp->toArray()
                        : null,
                ];
            }

            /** @inheritDoc */
            public static function fromArray(array $data): self
            {
                $thirdParamDto = new class () implements Dto
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

                $dto             = new self();
                $dto->firstProp  = $data['firstProp'];
                $dto->secondProp = $data['secondProp'];
                $dto->thirdProp  = isset($data['thirdProp'])
                    ? $thirdParamDto::fromArray($data['thirdProp'])
                    : null;

                return $dto;
            }
        };
    }
}
