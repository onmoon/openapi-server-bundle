<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Unit\Specification;

use cebe\openapi\spec\MediaType;
use cebe\openapi\spec\OpenApi;
use cebe\openapi\spec\Operation;
use cebe\openapi\spec\Parameter;
use cebe\openapi\spec\Paths;
use cebe\openapi\spec\Reference;
use cebe\openapi\spec\RequestBody;
use cebe\openapi\spec\Response;
use cebe\openapi\spec\Responses;
use cebe\openapi\spec\Schema;
use cebe\openapi\spec\Type;
use DateTimeImmutable;
use OnMoon\OpenApiServerBundle\Exception\CannotParseOpenApi;
use OnMoon\OpenApiServerBundle\Specification\Definitions\ObjectSchema;
use OnMoon\OpenApiServerBundle\Specification\Definitions\SpecificationConfig;
use OnMoon\OpenApiServerBundle\Specification\SpecificationParser;
use OnMoon\OpenApiServerBundle\Types\ScalarTypesResolver;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use stdClass;

use function array_map;
use function sprintf;

/**
 * @covers \OnMoon\OpenApiServerBundle\Specification\SpecificationParser
 */
final class SpecificationParserTest extends TestCase
{
    public function testParseOpenApiSuccess(): void
    {
        $specificationName   = 'SomeCustomSpecification';
        $specificationConfig = new SpecificationConfig(
            '/some/custom/specification/path',
            null,
            '\\Some\\Custom\\Namespace',
            'application/json',
        );
        $parsedSpecification = new OpenApi([
            'paths' => new Paths([
                '/some/custom/url' => [
                    'post' => new Operation([
                        'operationId' => 'SomeCustomOperationWithRequestAndResponses',
                        'requestBody' => new RequestBody([
                            'description' => 'SomeCustomRequestParam',
                            'content' => [
                                'application/json' => new MediaType([
                                    'schema' => new Schema([
                                        'type' => Type::OBJECT,
                                        'properties' => [
                                            'someProperty1' => new Schema([
                                                'type' => Type::STRING,
                                                'default' => 'SomeDefaultValue',
                                                'readOnly' => false,
                                                'writeOnly' => true,
                                                'description' => 'SomeCustomDescription',
                                                'nullable' => true,
                                                'pattern' => 'SomeCustomPattern',
                                            ]),
                                            'someProperty2' => new Schema([
                                                'type' => Type::INTEGER,
                                                'default' => 1000,
                                                'readOnly' => false,
                                            ]),
                                            'someReadOnlyProperty' => new Schema([
                                                'type' => Type::STRING,
                                                'default' => 'SomeDefaultValue',
                                                'readOnly' => true,
                                            ]),
                                            'someWriteOnlyProperty' => new Schema([
                                                'type' => Type::STRING,
                                                'default' => 'SomeDefaultValue',
                                                'writeOnly' => true,
                                            ]),
                                            'someProperty3' => new Schema([
                                                'type' => Type::OBJECT,
                                                'readOnly' => false,
                                                'properties' => [
                                                    'someSubProperty1' => new Schema([
                                                        'type' => Type::INTEGER,
                                                    ]),
                                                    'someSubProperty2' => new Schema([
                                                        'type' => Type::INTEGER,
                                                    ]),
                                                    'someArrayProperty' => new Schema([
                                                        'type' => Type::ARRAY,
                                                        'items' => new Schema([
                                                            'type' => Type::INTEGER,
                                                        ]),
                                                    ]),
                                                    'someDateTimeProperty' => new Schema([
                                                        'type' => Type::STRING,
                                                        'format' => 'date-time',
                                                        'default' => 1605101247,
                                                    ]),
                                                ],
                                            ]),
                                        ],
                                    ]),
                                ]),
                            ],
                        ]),
                        'responses' => new Responses([
                            '200' => new Response([
                                'description' => 'SomeCustomResponseParam200',
                                'content' => [
                                    'application/json' => new MediaType([
                                        'schema' => new Schema([
                                            'type' => Type::OBJECT,
                                            'properties' => [
                                                'someProperty' => new Schema([
                                                    'type' => Type::STRING,
                                                    'default' => 'SomeDefaultValue',
                                                    'writeOnly' => false,
                                                ]),
                                                'someWriteOnlyProperty' => new Schema([
                                                    'type' => Type::STRING,
                                                    'default' => 'SomeDefaultValue',
                                                    'writeOnly' => true,
                                                ]),
                                            ],
                                        ]),
                                    ]),
                                ],
                            ]),
                            '404' => new Response([
                                'description' => 'SomeCustomResponseParam404',
                                'content' => [
                                    'application/json' => new MediaType(['schema' => null]),
                                ],
                            ]),
                            '201' => new Response([
                                'description' => 'SomeCustomResponseParam201',
                                'content' => [
                                    'application/json' => new MediaType([
                                        'schema' => new Schema([
                                            'type' => Type::OBJECT,
                                        ]),
                                    ]),
                                ],
                            ]),
                        ]),
                        'parameters' => [
                            new Parameter([
                                'name' => 'queryParam',
                                'in' => 'query',
                                'schema' => new Schema([
                                    'type' => Type::INTEGER,
                                    'default' => 'SomeDefaultValue2',
                                ]),
                            ]),
                            new Parameter([
                                'name' => 'pathParam',
                                'in' => 'path',
                                'schema' => new Schema([
                                    'type' => Type::INTEGER,
                                    'default' => 'SomeDefaultValue2',
                                ]),
                            ]),
                            [
                                '$ref' => 'SomeCustomQueryParamReference',
                                'name' => 'secondQueryParam',
                                'in' => 'query',
                                'schema' => new Schema([
                                    'type' => Type::INTEGER,
                                    'default' => 'SomeDefaultValue3',
                                ]),
                            ],
                            new Parameter([
                                'name' => 'undefinedParam',
                                'in' => 'undefined',
                                'schema' => new Schema([
                                    'type' => Type::INTEGER,
                                    'default' => 'SomeDefaultValue3',
                                ]),
                            ]),
                        ],
                    ]),
                    'parameters' => [
                        new Parameter([
                            'name' => 'pathItemQueryParam',
                            'in' => 'query',
                            'schema' => new Schema([
                                'type' => Type::INTEGER,
                                'default' => 'SomeDefaultValue',
                            ]),
                        ]),
                        new Parameter([
                            'name' => 'queryParam',
                            'in' => 'query',
                            'schema' => new Schema([
                                'type' => Type::INTEGER,
                                'default' => 'SomeDefaultPathQueryValue',
                            ]),
                        ]),
                    ],
                ],
            ]),
        ]);

        $specificationParser = new SpecificationParser(new ScalarTypesResolver(), []);

        $specification = $specificationParser->parseOpenApi(
            $specificationName,
            $specificationConfig,
            $parsedSpecification
        );

        Assert::assertSame(
            'SomeCustomSpecification.SomeCustomOperationWithRequestAndResponses',
            $specification->getOperation('SomeCustomOperationWithRequestAndResponses')->getRequestHandlerName()
        );

        $requestBody = $specification
            ->getOperation('SomeCustomOperationWithRequestAndResponses')
            ->getRequestBody();
        Assert::assertNotNull($requestBody);

        $requestBodyProperties = $requestBody->getProperties();
        Assert::assertSame('someProperty1', $requestBodyProperties[0]->getName());
        Assert::assertSame('SomeCustomDescription', $requestBodyProperties[0]->getDescription());
        Assert::assertTrue($requestBodyProperties[0]->isNullable());
        Assert::assertSame('SomeCustomPattern', $requestBodyProperties[0]->getPattern());

        Assert::assertSame('someProperty2', $requestBodyProperties[1]->getName());
        Assert::assertSame(10, $requestBodyProperties[1]->getScalarTypeId());

        Assert::assertFalse($requestBodyProperties[1]->isRequired());

        Assert::assertSame('someProperty3', $requestBodyProperties[3]->getName());

        /** @var ObjectSchema $objectTypeDefinition */
        $objectTypeDefinition = $requestBodyProperties[3]->getObjectTypeDefinition();

        Assert::assertTrue($objectTypeDefinition->getProperties()[2]->isArray());
        Assert::assertSame('someDateTimeProperty', $objectTypeDefinition->getProperties()[3]->getName());
        Assert::assertSame('2020-11-11T13:27:27+00:00', $objectTypeDefinition->getProperties()[3]->getDefaultValue());

        Assert::assertNotNull($requestBodyProperties[3]->getObjectTypeDefinition());
        Assert::assertCount(4, $requestBodyProperties[3]->getObjectTypeDefinition()->getProperties());

        foreach ($requestBody->getProperties() as $property) {
            Assert::assertNotContains($property->getName(), ['someReadOnlyProperty']);
        }

        foreach (
            $specification->getOperation('SomeCustomOperationWithRequestAndResponses')
                ->getResponse('200')
                ->getProperties() as $property
        ) {
            Assert::assertContains($property->getName(), ['someProperty']);
            Assert::assertNotContains($property->getName(), ['someWriteOnlyProperty']);
        }

        foreach (['200', '201'] as $code) {
            Assert::assertArrayHasKey(
                $code,
                $specification
                    ->getOperation('SomeCustomOperationWithRequestAndResponses')
                    ->getResponses()
            );
        }

        $requestParams = $specification
            ->getOperation('SomeCustomOperationWithRequestAndResponses')
            ->getRequestParameters();

        Assert::assertSame(
            'pathItemQueryParam',
            $requestParams['query']->getProperties()[0]->getName()
        );
        Assert::assertSame(
            'queryParam',
            $requestParams['query']->getProperties()[1]->getName()
        );

        Assert::assertArrayNotHasKey(
            2,
            $requestParams['query']->getProperties()
        );

        Assert::assertSame(
            'pathParam',
            $requestParams['path']->getProperties()[2]->getName()
        );
    }

    public function testParseOpenApiWithCustomDateTimeClassSuccess(): void
    {
        $specificationName   = 'SomeCustomSpecification';
        $specificationConfig = new SpecificationConfig(
            '/some/custom/specification/path',
            null,
            '\\Some\\Custom\\Namespace',
            'application/json',
            DateTimeImmutable::class
        );
        $parsedSpecification = new OpenApi([
            'paths' => new Paths([
                '/some/custom/url' => [
                    'post' => new Operation([
                        'operationId' => 'SomeCustomOperationWithRequestAndResponses',
                        'requestBody' => new RequestBody([
                            'description' => 'SomeCustomRequestParam',
                            'content' => [
                                'application/json' => new MediaType([
                                    'schema' => new Schema([
                                        'type' => Type::OBJECT,
                                        'properties' => [
                                            'someDateTimeProperty' => new Schema([
                                                'type' => Type::STRING,
                                                'format' => 'date-time',
                                                'default' => 1605101247,
                                            ]),
                                        ],
                                    ]),
                                ]),
                            ],
                        ]),
                        'responses' => new Responses([
                            '200' => new Response([
                                'description' => 'SomeCustomResponseParam200',
                                'content' => [
                                    'application/json' => new MediaType([
                                        'schema' => new Schema([
                                            'type' => Type::OBJECT,
                                            'properties' => [
                                                'someDateTimeProperty' => new Schema([
                                                    'type' => Type::STRING,
                                                    'format' => 'date-time',
                                                    'default' => 1605101247,
                                                ]),
                                            ],
                                        ]),
                                    ]),
                                ],
                            ]),
                        ]),
                    ]),
                ],
            ]),
        ]);

        $specificationParser = new SpecificationParser(new ScalarTypesResolver(), []);

        $specification = $specificationParser->parseOpenApi(
            $specificationName,
            $specificationConfig,
            $parsedSpecification
        );

        $requestBody = $specification
            ->getOperation('SomeCustomOperationWithRequestAndResponses')
            ->getRequestBody();
        Assert::assertNotNull($requestBody);

        $requestBodyProperties = $requestBody->getProperties();
        Assert::assertSame(DateTimeImmutable::class, $requestBodyProperties[0]->getOutputType());
    }

    public function testParseOpenApiSuccessRequestBadMediaType(): void
    {
        $specificationName   = 'SomeCustomSpecification';
        $specificationConfig = new SpecificationConfig(
            '/some/custom/specification/path',
            null,
            '\\Some\\Custom\\Namespace',
            'application/json',
        );
        $parsedSpecification = new OpenApi([
            'paths' => new Paths([
                '/some/custom/bad-media-type/url' => [
                    'post' => new Operation([
                        'operationId' => 'SomeCustomOperationRequestBadMediaType',
                        'requestBody' => new RequestBody([
                            'description' => 'SomeCustomRequestParam',
                            'content' => [
                                'application/bad-media-type' => new MediaType([
                                    'schema' => new Schema([
                                        'type' => Type::OBJECT,
                                    ]),
                                ]),
                                'application/json' => [
                                    '$ref' => 'SomeCustomRequestParamReference',
                                    'schema' => new Schema([
                                        'type' => Type::OBJECT,
                                    ]),
                                ],
                            ],
                        ]),
                    ]),
                ],
            ]),
        ]);

        $specificationParser = new SpecificationParser(new ScalarTypesResolver(), []);

        $specification = $specificationParser->parseOpenApi(
            $specificationName,
            $specificationConfig,
            $parsedSpecification
        );

        Assert::assertNull(
            $specification
                ->getOperation('SomeCustomOperationRequestBadMediaType')
                ->getRequestBody()
        );
    }

    public function testParseOpenApiSuccessRequestBadAndGoodMediaType(): void
    {
        $specificationName   = 'SomeCustomSpecification';
        $specificationConfig = new SpecificationConfig(
            '/some/custom/specification/path',
            null,
            '\\Some\\Custom\\Namespace',
            'application/json',
        );
        $parsedSpecification = new OpenApi([
            'paths' => new Paths([
                '/some/custom/bad-media-type/url' => [
                    'post' => new Operation([
                        'operationId' => 'SomeCustomOperationRequestBadMediaType',
                        'requestBody' => new RequestBody([
                            'description' => 'SomeCustomRequestParam',
                            'content' => [
                                'application/bad-media-type' => new MediaType([
                                    'schema' => new Schema([
                                        'type' => Type::OBJECT,
                                    ]),
                                ]),
                                'application/json' => new MediaType([
                                    'schema' => new Schema([
                                        'type' => Type::OBJECT,
                                    ]),
                                ]),
                            ],
                        ]),
                    ]),
                ],
            ]),
        ]);

        $specificationParser = new SpecificationParser(new ScalarTypesResolver(), []);

        $specification = $specificationParser->parseOpenApi(
            $specificationName,
            $specificationConfig,
            $parsedSpecification
        );

        Assert::assertInstanceOf(
            ObjectSchema::class,
            $specification
                ->getOperation('SomeCustomOperationRequestBadMediaType')
                ->getRequestBody()
        );
    }

    /**
     * @return mixed[]
     */
    public function parseOpenApiSuccessDefaultValueProvider(): array
    {
        return [
            [
                'payload' => [
                    'responseProperties' => [
                        'somePropertyA' => [
                            'type' => Type::INTEGER,
                            'format' => null,
                            'default' => 'SomeDefaultValue',
                            'expected' => [
                                'hasObjectTypeDefinitionInstance' => false,
                                'default' => 'SomeDefaultValue',
                            ],
                        ],
                        'somePropertyB' => [
                            'type' => Type::INTEGER,
                            'format' => null,
                            'default' => null,
                            'expected' => [
                                'hasObjectTypeDefinitionInstance' => false,
                                'default' => null,
                            ],
                        ],
                        'somePropertyC' => [
                            'type' => Type::NUMBER,
                            'format' => 'int32',
                            'default' => 'SomeDefaultValue',
                            'expected' => [
                                'hasObjectTypeDefinitionInstance' => false,
                                'default' => 'SomeDefaultValue',
                            ],
                        ],
                        'somePropertyD' => [
                            'type' => Type::NUMBER,
                            'format' => 'int32',
                            'default' => null,
                            'expected' => [
                                'hasObjectTypeDefinitionInstance' => false,
                                'default' => null,
                            ],
                        ],
                        'somePropertyE' => [
                            'type' => Type::OBJECT,
                            'format' => null,
                            'default' => 'SomeDefaultValue',
                            'expected' => [
                                'hasObjectTypeDefinitionInstance' => true,
                                'default' => null,
                            ],
                        ],
                        'somePropertyF' => [
                            'type' => Type::OBJECT,
                            'format' => null,
                            'default' => null,
                            'expected' => [
                                'hasObjectTypeDefinitionInstance' => true,
                                'default' => null,
                            ],
                        ],
                        'somePropertyG' => [
                            'type' => Type::NUMBER,
                            'format' => 'undefinedFormat',
                            'default' => 'SomeDefaultValue',
                            'expected' => [
                                'hasObjectTypeDefinitionInstance' => false,
                                'default' => 'SomeDefaultValue',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @param mixed[] $payload
     *
     * @dataProvider parseOpenApiSuccessDefaultValueProvider
     */
    public function testParseOpenApiSuccessDefaultValue(array $payload): void
    {
        $specification = (new SpecificationParser(new ScalarTypesResolver(), []))->parseOpenApi(
            'SomeCustomSpecification',
            new SpecificationConfig(
                '/some/custom/specification/path',
                null,
                '\\Some\\Custom\\Namespace',
                'some/media-type',
            ),
            new OpenApi([
                'paths' => new Paths([
                    '/some/custom/third/url' => [
                        'post' => new Operation([
                            'operationId' => 'SomeCustomThirdPostOperation',
                            'responses' => new Responses([
                                '200' => new Response([
                                    'description' => 'SomeCustomResponseParam',
                                    'content' => [
                                        'some/media-type' => new MediaType([
                                            'schema' => new Schema([
                                                'type' => Type::OBJECT,
                                                'properties' => array_map(
                                                    static function (array $data): Schema {
                                                        return new Schema([
                                                            'type' => $data['type'],
                                                            'format' => $data['format'],
                                                            'default' => $data['default'],
                                                        ]);
                                                    },
                                                    $payload['responseProperties']
                                                ),
                                                'required' => false,
                                            ]),
                                        ]),
                                    ],
                                ]),
                            ]),
                        ]),
                    ],
                ]),
            ])
        );

        foreach ($specification->getOperation('SomeCustomThirdPostOperation')->getResponse('200')->getProperties() as $propertyName => $property) {
            if ((bool) $payload['responseProperties'][$property->getName()]['expected']['hasObjectTypeDefinitionInstance']) {
                Assert::assertInstanceOf(ObjectSchema::class, $property->getObjectTypeDefinition());
            }

            Assert::assertSame(
                $payload['responseProperties'][$property->getName()]['expected']['default'],
                $property->getDefaultValue()
            );
        }
    }

    /**
     * @return mixed[]
     */
    public function parseOpenApiThrowCannotParseOpenApiProvider(): array
    {
        return [
            [
                'paths' => [
                    '/some/custom/url' => [
                        'get' => new Operation([
                            'operationId' => 'SomeCustomOperation',
                            'parameters' => [
                                new Parameter([
                                    'name' => 'NotScalarQueryParam',
                                    'in' => 'query',
                                    'schema' => new Schema([
                                        'type' => Type::OBJECT,
                                        'default' => 'SomeDefaultValue',
                                    ]),
                                ]),
                            ],
                        ]),
                    ],
                ],
                'expected' => ['exceptionMessage' => null],
            ],
            [
                'paths' => [
                    '/some/custom/url' => [
                        'post' => new Operation([
                            'operationId' => 'SomeCustomOperation',
                            'requestBody' => new RequestBody([
                                'description' => 'SomeCustomRequestParam',
                                'content' => [
                                    'application/json' => new MediaType([
                                        'schema' => new Schema([
                                            'type' => Type::INTEGER,
                                        ]),
                                    ]),
                                ],
                            ]),
                        ]),
                    ],
                ],
                'expected' => ['exceptionMessage' => null],
            ],
            [
                'paths' => [
                    '/some/custom/url' => [
                        'post' => new Operation([
                            'operationId' => 'SomeCustomOperation',
                            'responses' => new Responses([
                                '200' => new Response([
                                    'description' => 'SomeCustomResponseParam',
                                    'content' => [
                                        'application/json' => new MediaType([
                                            'schema' => new Schema([
                                                'type' => Type::INTEGER,
                                            ]),
                                        ]),
                                    ],
                                ]),
                            ]),
                        ]),
                    ],
                ],
                'expected' => ['exceptionMessage' => null],
            ],
            [
                'paths' => [
                    '/some/custom/url' => [
                        'post' => new Operation([
                            'operationId' => 'SomeCustomOperationWithException',
                            'responses' => new Responses([
                                '200' => new Response([
                                    'description' => 'SomeCustomResponseParam',
                                    'content' => [
                                        'application/json' => new MediaType([
                                            'schema' => new Schema([
                                                'type' => Type::ARRAY,
                                            ]),
                                        ]),
                                    ],
                                ]),
                            ]),
                        ]),
                    ],
                ],
                'expected' => ['exceptionMessage' => 'Only object is allowed as root in response (code "200") (array as root is insecure, see https://haacked.com/archive/2009/06/25/json-hijacking.aspx/) for operation: "post" of path: "/some/custom/url" in specification file: "/some/custom/specification/path".'],
            ],
        ];
    }

    /**
     * @param Operation[][][][] $paths
     * @param mixed[]           $expected
     *
     * @dataProvider parseOpenApiThrowCannotParseOpenApiProvider
     */
    public function testParseOpenApiThrowCannotParseOpenApi(array $paths, array $expected): void
    {
        $specificationName   = 'SomeCustomSpecification';
        $specificationConfig = new SpecificationConfig(
            '/some/custom/specification/path',
            null,
            '\\Some\\Custom\\Namespace',
            'application/json',
        );
        $parsedSpecification = new OpenApi([
            'paths' => new Paths($paths),
        ]);

        $specificationParser = new SpecificationParser(new ScalarTypesResolver(), []);

        if ($expected['exceptionMessage'] === null) {
            $this->expectException(CannotParseOpenApi::class);
        } else {
            $this->expectExceptionMessage($expected['exceptionMessage']);
        }

        $specificationParser->parseOpenApi(
            $specificationName,
            $specificationConfig,
            $parsedSpecification
        );
    }

    public function testParseOpenApiThrowExceptionNoOperationIdSpecified(): void
    {
        $specificationName   = 'SomeCustomSpecification';
        $specificationConfig = new SpecificationConfig(
            '/some/custom/specification/path',
            null,
            '\\Some\\Custom\\Namespace',
            'some/media-type',
        );
        $parsedSpecification = new OpenApi([
            'paths' => new Paths([
                '/some/get/url' => [
                    'get' => new Operation(['operationId' => '']),
                ],
            ]),
        ]);

        $specificationParser = new SpecificationParser(new ScalarTypesResolver(), []);

        $this->expectException(CannotParseOpenApi::class);

        $specificationParser->parseOpenApi(
            $specificationName,
            $specificationConfig,
            $parsedSpecification
        );
    }

    public function testParseOpenApiThrowExceptionPropertyIsNotScheme(): void
    {
        $specificationName   = 'SomeCustomSpecification';
        $specificationConfig = new SpecificationConfig(
            '/some/custom/specification/path',
            null,
            '\\Some\\Custom\\Namespace',
            'some/media-type',
        );
        $parsedSpecification = new OpenApi([
            'paths' => new Paths([
                '/some/custom/third/url' => [
                    'post' => new Operation([
                        'operationId' => 'SomeCustomThirdPostOperation',
                        'responses' => new Responses([
                            '200' => new Response([
                                'description' => 'SomeCustomResponseParam',
                                'content' => [
                                    'some/media-type' => new MediaType([
                                        'schema' => new Schema([
                                            'type' => Type::OBJECT,
                                            'properties' => [new Reference(['$ref' => 'some_reference'], null)],
                                        ]),
                                    ]),
                                ],
                            ]),
                        ]),
                    ]),
                ],
            ]),
        ]);

        $specificationParser = new SpecificationParser(new ScalarTypesResolver(), []);

        $this->expectException(CannotParseOpenApi::class);
        $this->expectExceptionMessage('Property is not scheme');

        $specificationParser->parseOpenApi(
            $specificationName,
            $specificationConfig,
            $parsedSpecification
        );
    }

    public function testParseOpenApiWithReferenceInParametersThrowExceptionPropertyIsNotScheme(): void
    {
        $specificationName   = 'SomeCustomSpecification';
        $specificationConfig = new SpecificationConfig(
            '/some/custom/specification/path',
            null,
            '\\Some\\Custom\\Namespace',
            'some/media-type',
        );
        $parsedSpecification = new OpenApi([
            'paths' => new Paths([
                '/some/custom/third/url' => [
                    'post' => new Operation([
                        'operationId' => 'SomeCustomThirdPostOperation',
                        'parameters' => [
                            new Parameter([
                                'name' => 'queryParam',
                                'in' => 'query',
                                'schema' => new Reference(['$ref' => 'some_reference'], null),
                            ]),
                        ],
                    ]),
                ],
            ]),
        ]);

        $specificationParser = new SpecificationParser(new ScalarTypesResolver(), []);

        $this->expectException(CannotParseOpenApi::class);
        $this->expectExceptionMessage('Property is not scheme');

        $specificationParser->parseOpenApi(
            $specificationName,
            $specificationConfig,
            $parsedSpecification
        );
    }

    public function testParseOpenApiThrowExceptionArrayIsNotDescribed(): void
    {
        $specificationName   = 'SomeCustomSpecification';
        $specificationConfig = new SpecificationConfig(
            '/some/custom/specification/path',
            null,
            '\\Some\\Custom\\Namespace',
            'some/media-type',
        );
        $parsedSpecification = new OpenApi([
            'paths' => new Paths([
                '/some/custom/third/url' => [
                    'post' => new Operation([
                        'operationId' => 'SomeCustomThirdPostOperation',
                        'parameters' => [
                            new Parameter([
                                'name' => 'queryParam',
                                'in' => 'query',
                                'schema' => new Schema([
                                    'type' => Type::ARRAY,
                                ]),
                            ]),
                        ],
                    ]),
                ],
            ]),
        ]);

        $specificationParser = new SpecificationParser(new ScalarTypesResolver(), []);

        $this->expectException(CannotParseOpenApi::class);
        $this->expectExceptionMessage(
            'Cannot generate property for DTO class, property "queryParam" is array without items description in ' .
            'request query parameters for operation: "post" of path: "/some/custom/third/url" in specification file: ' .
            '"/some/custom/specification/path".'
        );

        $specificationParser->parseOpenApi(
            $specificationName,
            $specificationConfig,
            $parsedSpecification
        );
    }

    public function testParseOpenApiThrowExceptionPropertyNotScalar(): void
    {
        $specificationName   = 'SomeCustomSpecification';
        $specificationConfig = new SpecificationConfig(
            '/some/custom/specification/path',
            null,
            '\\Some\\Custom\\Namespace',
            'some/media-type',
        );
        $parsedSpecification = new OpenApi([
            'paths' => new Paths([
                '/some/custom/third/url' => [
                    'post' => new Operation([
                        'operationId' => 'SomeCustomThirdPostOperation',
                        'parameters' => [
                            new Parameter([
                                'name' => 'queryParam',
                                'in' => 'query',
                                'schema' => new Schema([
                                    'type' => Type::ARRAY,
                                    'items' => new Schema([
                                        'type' => Type::INTEGER,
                                    ]),
                                ]),
                            ]),
                        ],
                    ]),
                ],
            ]),
        ]);

        $specificationParser = new SpecificationParser(new ScalarTypesResolver(), []);

        $this->expectException(CannotParseOpenApi::class);
        $this->expectExceptionMessage(
            'Cannot generate property for DTO class, property "queryParam" is not scalar in ' .
            'request query parameters for operation: "post" of path: "/some/custom/third/url" in specification file: ' .
            '"/some/custom/specification/path".'
        );

        $specificationParser->parseOpenApi(
            $specificationName,
            $specificationConfig,
            $parsedSpecification
        );
    }

    public function testParseOpenApiThrowExceptionTypeNotSupported(): void
    {
        $specificationName   = 'SomeCustomSpecification';
        $specificationConfig = new SpecificationConfig(
            '/some/custom/specification/path',
            null,
            '\\Some\\Custom\\Namespace',
            'some/media-type',
        );
        $parsedSpecification = new OpenApi([
            'paths' => new Paths([
                '/some/custom/third/url' => [
                    'post' => new Operation([
                        'operationId' => 'SomeCustomThirdPostOperation',
                        'parameters' => [
                            new Parameter([
                                'name' => 'queryParam',
                                'in' => 'query',
                                'schema' => new Schema(['type' => 'unsupported_type']),
                            ]),
                        ],
                    ]),
                ],
            ]),
        ]);

        $specificationParser = new SpecificationParser(new ScalarTypesResolver(), []);

        $this->expectException(CannotParseOpenApi::class);
        $this->expectExceptionMessage(
            'Cannot generate property for DTO class, property "queryParam" type "unsupported_type" is not supported in ' .
            'request query parameters for operation: "post" of path: "/some/custom/third/url" in specification file: ' .
            '"/some/custom/specification/path".'
        );

        $specificationParser->parseOpenApi(
            $specificationName,
            $specificationConfig,
            $parsedSpecification
        );
    }

    public function testParseOpenApiThrowExceptionDuplicateOperationId(): void
    {
        $specificationName   = 'SomeCustomSpecification';
        $specificationConfig = new SpecificationConfig(
            '/some/custom/specification/path',
            null,
            '\\Some\\Custom\\Namespace',
            'some/media-type',
        );
        $parsedSpecification = new OpenApi([
            'paths' => new Paths([
                '/some/get/url' => [
                    'get' => new Operation(['operationId' => 'SomeCustomOperationId']),
                ],
                '/some/get/url/second' => [
                    'get' => new Operation(['operationId' => 'SomeCustomOperationId']),
                ],
            ]),
        ]);

        $specificationParser = new SpecificationParser(new ScalarTypesResolver(), []);

        $this->expectException(CannotParseOpenApi::class);

        $specificationParser->parseOpenApi(
            $specificationName,
            $specificationConfig,
            $parsedSpecification
        );
    }

    public function testParseOpenApiWithCustomDateTimeClassThrowExceptionUnknownType(): void
    {
        $specificationName   = 'SomeCustomSpecification';
        $specificationConfig = new SpecificationConfig(
            '/some/custom/specification/path',
            null,
            '\\Some\\Custom\\Namespace',
            'application/json',
            'SomeNotExistedDateTimeClass'
        );
        $parsedSpecification = new OpenApi([
            'paths' => new Paths([
                '/some/custom/url' => [
                    'post' => new Operation([
                        'operationId' => 'SomeCustomOperationWithRequestAndResponses',
                        'requestBody' => new RequestBody([
                            'description' => 'SomeCustomRequestParam',
                            'content' => [
                                'application/json' => new MediaType([
                                    'schema' => new Schema([
                                        'type' => Type::OBJECT,
                                        'properties' => [
                                            'someDateTimeProperty' => new Schema([
                                                'type' => Type::STRING,
                                                'format' => 'date-time',
                                                'default' => 1605101247,
                                            ]),
                                        ],
                                    ]),
                                ]),
                            ],
                        ]),
                        'responses' => new Responses([
                            '200' => new Response([
                                'description' => 'SomeCustomResponseParam200',
                                'content' => [
                                    'application/json' => new MediaType([
                                        'schema' => new Schema([
                                            'type' => Type::OBJECT,
                                            'properties' => [
                                                'someDateTimeProperty' => new Schema([
                                                    'type' => Type::STRING,
                                                    'format' => 'date-time',
                                                    'default' => 1605101247,
                                                ]),
                                            ],
                                        ]),
                                    ]),
                                ],
                            ]),
                        ]),
                    ]),
                ],
            ]),
        ]);

        $specificationParser = new SpecificationParser(new ScalarTypesResolver(), []);

        $this->expectException(CannotParseOpenApi::class);
        $this->expectExceptionMessage('Class "SomeNotExistedDateTimeClass" does not exist');

        $specificationParser->parseOpenApi(
            $specificationName,
            $specificationConfig,
            $parsedSpecification
        );
    }

    public function testParseOpenApiWithCustomDateTimeClassThrowExceptionTypeNotSupported(): void
    {
        $specificationName    = 'SomeCustomSpecification';
        $someNotDateTimeClass = $this->getMockBuilder(stdClass::class)->getMock();
        $specificationConfig  = new SpecificationConfig(
            '/some/custom/specification/path',
            null,
            '\\Some\\Custom\\Namespace',
            'application/json',
            $someNotDateTimeClass::class
        );
        $parsedSpecification  = new OpenApi([
            'paths' => new Paths([
                '/some/custom/url' => [
                    'post' => new Operation([
                        'operationId' => 'SomeCustomOperationWithRequestAndResponses',
                        'requestBody' => new RequestBody([
                            'description' => 'SomeCustomRequestParam',
                            'content' => [
                                'application/json' => new MediaType([
                                    'schema' => new Schema([
                                        'type' => Type::OBJECT,
                                        'properties' => [
                                            'someDateTimeProperty' => new Schema([
                                                'type' => Type::STRING,
                                                'format' => 'date-time',
                                                'default' => 1605101247,
                                            ]),
                                        ],
                                    ]),
                                ]),
                            ],
                        ]),
                        'responses' => new Responses([
                            '200' => new Response([
                                'description' => 'SomeCustomResponseParam200',
                                'content' => [
                                    'application/json' => new MediaType([
                                        'schema' => new Schema([
                                            'type' => Type::OBJECT,
                                            'properties' => [
                                                'someDateTimeProperty' => new Schema([
                                                    'type' => Type::STRING,
                                                    'format' => 'date-time',
                                                    'default' => 1605101247,
                                                ]),
                                            ],
                                        ]),
                                    ]),
                                ],
                            ]),
                        ]),
                    ]),
                ],
            ]),
        ]);

        $specificationParser = new SpecificationParser(new ScalarTypesResolver(), []);

        $this->expectException(CannotParseOpenApi::class);
        $this->expectExceptionMessage(sprintf(
            'Cannot generate property for DTO class, property "someDateTimeProperty" type "%s" is not supported ' .
            'in response (code "200") for operation: "post" of path: "/some/custom/url" in specification file: ' .
            '"/some/custom/specification/path".',
            $someNotDateTimeClass::class
        ));

        $specificationParser->parseOpenApi(
            $specificationName,
            $specificationConfig,
            $parsedSpecification
        );
    }
}
