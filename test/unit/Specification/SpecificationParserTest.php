<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Unit\Specification;

use cebe\openapi\exceptions\TypeErrorException;
use cebe\openapi\spec\MediaType;
use cebe\openapi\spec\OpenApi;
use cebe\openapi\spec\Operation;
use cebe\openapi\spec\Parameter;
use cebe\openapi\spec\Paths;
use cebe\openapi\spec\RequestBody;
use cebe\openapi\spec\Response;
use cebe\openapi\spec\Responses;
use cebe\openapi\spec\Schema;
use cebe\openapi\spec\Type;
use OnMoon\OpenApiServerBundle\Exception\CannotParseOpenApi;
use OnMoon\OpenApiServerBundle\Specification\Definitions\ObjectType;
use OnMoon\OpenApiServerBundle\Specification\Definitions\SpecificationConfig;
use OnMoon\OpenApiServerBundle\Specification\SpecificationParser;
use OnMoon\OpenApiServerBundle\Types\ScalarTypesResolver;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OnMoon\OpenApiServerBundle\Specification\SpecificationParser
 */
final class SpecificationParserTest extends TestCase
{
    private ScalarTypesResolver $typeResolver;

    public function setUp(): void
    {
        parent::setUp();

        $this->typeResolver = new ScalarTypesResolver();
    }

    public function tearDown(): void
    {
        unset($this->typeResolver);

        parent::tearDown();
    }

    /**
     * @throws CannotParseOpenApi
     * @throws TypeErrorException
     */
    public function testParseOpenApiSuccessBasic(): void
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
                    'get' => new Operation(['operationId' => 'SomeUrlGetBasic']),
                    'post' => new Operation(['operationId' => 'SomeUrlPostBasic']),
                ],
            ]),
        ]);

        $specificationParser = new SpecificationParser($this->typeResolver);

        $specification = $specificationParser->parseOpenApi(
            $specificationName,
            $specificationConfig,
            $parsedSpecification
        );

        Assert::assertSame(
            '/some/custom/url',
            $specification->getOperation('SomeUrlGetBasic')->getUrl()
        );
        Assert::assertSame(
            '/some/custom/url',
            $specification->getOperation('SomeUrlPostBasic')->getUrl()
        );
    }

    /**
     * @throws CannotParseOpenApi
     * @throws TypeErrorException
     */
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

        $specificationParser = new SpecificationParser($this->typeResolver);

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
        Assert::assertSame('someProperty1', $requestBodyProperties[0]->getName());
        Assert::assertSame('SomeCustomDescription', $requestBodyProperties[0]->getDescription());
        Assert::assertTrue($requestBodyProperties[0]->isNullable());
        Assert::assertSame('SomeCustomPattern', $requestBodyProperties[0]->getPattern());

        Assert::assertSame('someProperty2', $requestBodyProperties[1]->getName());
        Assert::assertSame(10, $requestBodyProperties[1]->getScalarTypeId());

        Assert::assertSame('someProperty3', $requestBodyProperties[2]->getName());

        Assert::assertNotNull($requestBodyProperties[2]->getObjectTypeDefinition());
        Assert::assertCount(2, $requestBodyProperties[2]->getObjectTypeDefinition()->getProperties());

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

    /**
     * @throws CannotParseOpenApi
     * @throws TypeErrorException
     */
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

        $specificationParser = new SpecificationParser($this->typeResolver);

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

    /**
     * @throws CannotParseOpenApi
     * @throws TypeErrorException
     */
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

        $specificationParser = new SpecificationParser($this->typeResolver);

        $specification = $specificationParser->parseOpenApi(
            $specificationName,
            $specificationConfig,
            $parsedSpecification
        );

        Assert::assertInstanceOf(
            ObjectType::class,
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
     * @throws CannotParseOpenApi
     * @throws TypeErrorException
     *
     * @dataProvider parseOpenApiSuccessDefaultValueProvider
     */
    public function testParseOpenApiSuccessDefaultValue(array $payload): void
    {
        $specificationName   = 'SomeCustomSpecification';
        $specificationConfig = new SpecificationConfig(
            '/some/custom/specification/path',
            null,
            '\\Some\\Custom\\Namespace',
            'some/media-type',
        );

        $responseProperties = [];
        foreach ($payload['responseProperties'] as $propertyName => $data) {
            $responseProperties[$propertyName] = new Schema([
                'type' => $data['type'],
                'format' => $data['format'],
                'default' => $data['default'],
            ]);
        }

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
                                            'properties' => $responseProperties,
                                            'required' => false,
                                        ]),
                                    ]),
                                ],
                            ]),
                        ]),
                    ]),
                ],
            ]),
        ]);

        $specificationParser = new SpecificationParser($this->typeResolver);

        $specification = $specificationParser->parseOpenApi(
            $specificationName,
            $specificationConfig,
            $parsedSpecification
        );

        foreach ($specification->getOperation('SomeCustomThirdPostOperation')->getResponse('200')->getProperties() as $propertyName => $property) {
            if ($payload['responseProperties'][$property->getName()]['expected']['hasObjectTypeDefinitionInstance']) {
                Assert::assertInstanceOf(ObjectType::class, $property->getObjectTypeDefinition());
            }

            Assert::assertSame(
                $payload['responseProperties'][$property->getName()]['expected']['default'],
                $property->getDefaultValue()
            );
        }
    }

    /**
     * @return mixed[]
     *
     * @throws TypeErrorException
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
     * @throws CannotParseOpenApi
     * @throws TypeErrorException
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

        $specificationParser = new SpecificationParser($this->typeResolver);

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

    /**
     * @throws CannotParseOpenApi
     * @throws TypeErrorException
     */
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

        $specificationParser = new SpecificationParser($this->typeResolver);

        $this->expectException(CannotParseOpenApi::class);

        $specificationParser->parseOpenApi(
            $specificationName,
            $specificationConfig,
            $parsedSpecification
        );
    }

    /**
     * @throws CannotParseOpenApi
     * @throws TypeErrorException
     */
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

        $specificationParser = new SpecificationParser($this->typeResolver);

        $this->expectException(CannotParseOpenApi::class);

        $specificationParser->parseOpenApi(
            $specificationName,
            $specificationConfig,
            $parsedSpecification
        );
    }
}
