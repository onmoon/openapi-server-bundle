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
        $queryProperties               = [];
        $queryProperties['queryParam'] = new Property('queryParam');
        $queryProperties['queryParam']->setDefaultValue('e55e57d3b65ff510b257025746ffb6e1');

        $query = new ObjectType($queryProperties);

        $pathProperties              = [];
        $pathProperties['pathParam'] = new Property('pathParam');
        $pathProperties['pathParam']->setDefaultValue('e55e57d3b65ff510b257025746ffb6e1');

        $path = new ObjectType($pathProperties);

        $bodyProperties         = [];
        $bodyProperties['name'] = new Property('name');
        $bodyProperties['name']->setDefaultValue('bodyName');
        $bodyProperties['value'] = new Property('value');
        $bodyProperties['value']->setDefaultValue('bodyValue');

        $requestBody = new ObjectType($bodyProperties);

        return [
            [
                'requestQuery' => $query,
                'requestPath'  => $path,
                'requestBody' => $requestBody,
            ],
        ];
    }

    /**
     * @dataProvider createRequestDtoWithPathProvider
     */
    public function testCreateRequestDtoWithPath(
        ObjectType $requestQuery,
        ObjectType $requestPath,
        ObjectType $requestBody
    ): void {
        $request = new Request(
            [],
            [],
            ['_route_params' => (object) ['customParam' => 'customParam']],
            [],
            [],
            [],
            '{}'
        );

        $operation = new Operation(
            '/example/path',
            'POST',
            'PostRequest',
            null,
            $requestBody,
            ['query' => $requestQuery, 'path' => $requestPath],
            []
        );

        $resolver = new ScalarTypesResolver();

        $arrayDtoSerializer = new ArrayDtoSerializer(
            $resolver,
            true
        );

        /** @var Dto $requestDto */
        $requestDto = $arrayDtoSerializer->createRequestDto(
            $request,
            $operation,
            Dto::class
        );

//        Assert::assertSame(
//            $requestQuery->getProperties()['queryParam']->getDefaultValue(),
//            $requestDto->getQueryParameters()->getQueryParam()
//        );
//        Assert::assertSame(
//            $requestPath->getProperties()['pathParam']->getDefaultValue(),
//            $requestDto->getPathParameters()->getPathParam()
//        );
//        Assert::assertSame(
//            $requestBody->getProperties()['name']->getDefaultValue(),
//            $requestDto->getBody()->getName()
//        );
//        Assert::assertSame(
//            $requestBody->getProperties()['value']->getDefaultValue(),
//            $requestDto->getBody()->getValue()
//        );
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

    public function testCreateResponseFromDtoManyPropertyIsNull(): void
    {
        $okResponseDtoFQCN = $this->makeOKResponseDtoFCQN();

        /** @var ResponseDto $okResponseDto */
        $okResponseDto = $okResponseDtoFQCN::fromArray([
            self::OK_RESPONSE_DTO_FIRST_PROP => null,
            self::OK_RESPONSE_DTO_SECOND_PROP => null,
        ]);

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

        Assert::assertSame([
            self::OK_RESPONSE_DTO_FIRST_PROP => 'SomeFirstDefaultValue',
            self::OK_RESPONSE_DTO_SECOND_PROP => 'SomeSecondDefaultValue',
        ], $result);
    }

    private function makeRequestDtoFCQN(): ResponseDto
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

//    public function testCreateResponseFromDtoPropertyIsNotNull(): void
//    {
//        $okResponseDtoFQCN = $this->makeOKResponseDtoFCQN();
//
//        $arrayDtoSerializer = new ArrayDtoSerializer(new ScalarTypesResolver(), false);
//
//        $result = $arrayDtoSerializer->createResponseFromDto(
//            $okResponseDtoFQCN::fromArray([
//                self::OK_RESPONSE_DTO_FIRST_PROP => 'SomeFirstNotNullValue',
//                self::OK_RESPONSE_DTO_SECOND_PROP => null,
////                self::OK_RESPONSE_DTO_SECOND_PROP => [
////                    self::OK_RESPONSE_DTO_FIRST_PROP => null,
////                    self::OK_RESPONSE_DTO_SECOND_PROP => null,
////                ],
//            ]),
//            new Operation(
//                '/example/path',
//                'POST',
//                'PostRequestHandler',
//                null,
//                null,
//                [],
//                [
//                    $okResponseDtoFQCN::_getResponseCode() => new ObjectType([
//                        (new Property(self::OK_RESPONSE_DTO_FIRST_PROP))
//                            ->setDefaultValue('SomeFirstDefaultValue')
////                            ->setScalarTypeId(1)
//                            ->setObjectTypeDefinition(
//                                new ObjectType([
//                                    (new Property(self::OK_RESPONSE_DTO_FIRST_PROP))
//                                        ->setDefaultValue('SomeFirstDefaultSubValue')
//                                ])
//                            ),
////                        (new Property(self::OK_RESPONSE_DTO_SECOND_PROP))
////                            ->setDefaultValue('SomeSecondDefaultValue')
////                            ->setArray(true),
//                    ]),
//                ]
//            )
//        );
//
//        dump($result);exit;
//
//        Assert::assertSame([
//            self::OK_RESPONSE_DTO_FIRST_PROP => 'SomeNotNullValue',
//            self::OK_RESPONSE_DTO_SECOND_PROP => 'SomeNotNullValue',
//        ], $result);
//    }
}
