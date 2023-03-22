<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Unit\CodeGenerator;

use cebe\openapi\ReferenceContext;
use cebe\openapi\spec\OpenApi;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\ComponentDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\DtoDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\GraphDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\OperationDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\PropertyDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\RequestHandlerInterfaceDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\ResponseDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\ServiceSubscriberDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\SpecificationDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\GraphGenerator;
use OnMoon\OpenApiServerBundle\Specification\Definitions\ObjectSchema;
use OnMoon\OpenApiServerBundle\Specification\Definitions\Property;
use OnMoon\OpenApiServerBundle\Specification\Definitions\SpecificationConfig;
use OnMoon\OpenApiServerBundle\Specification\SpecificationLoader;
use OnMoon\OpenApiServerBundle\Specification\SpecificationParser;
use OnMoon\OpenApiServerBundle\Types\ScalarTypesResolver;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

use function array_map;
use function count;

/** @covers \OnMoon\OpenApiServerBundle\CodeGenerator\GraphGenerator */
class GraphGeneratorTest extends TestCase
{
    private const SPECIFICATION_PATH = '/';
    private const SPECIFICATION_NAME = 'test';

    /** @var SpecificationLoader|MockObject  */
    private $specificationLoader;
    private SpecificationConfig $specificationConfig;
    private Property $pathParameterProperty;
    private Property $pathProperty;
    private Property $bodyParameterProperty;
    private Property $bodyProperty;
    private DtoDefinition $pathParameterPropertyDefinition;
    private DtoDefinition $bodyParameterPropertyDefinition;
    private DtoDefinition $requestDefinition;
    private PropertyDefinition $pathPropertyDefinition;
    private PropertyDefinition $bodyPropertyDefinition;

    public function setUp(): void
    {
        $this->specificationLoader = $this->createMock(SpecificationLoader::class);

        $this->specificationConfig = new SpecificationConfig(
            self::SPECIFICATION_PATH,
            null,
            '/',
            'application/json'
        );

        $this->pathParameterProperty = new Property('goodId');
        $this->pathParameterProperty
            ->setRequired(true)
            ->setScalarTypeId(0);
        $this->pathParameterPropertyDefinition = new DtoDefinition([new PropertyDefinition($this->pathParameterProperty)]);

        $this->pathProperty = new Property('pathParameters');
        $this->pathProperty->setRequired(true);
        $this->pathPropertyDefinition = new PropertyDefinition($this->pathProperty);
        $this->pathPropertyDefinition->setObjectTypeDefinition($this->pathParameterPropertyDefinition);

        $this->bodyParameterProperty = new Property('id');
        $this->bodyParameterProperty->setScalarTypeId(0);
        $this->bodyParameterPropertyDefinition = new DtoDefinition([new PropertyDefinition($this->bodyParameterProperty)]);

        $this->bodyProperty = new Property('body');
        $this->bodyProperty->setRequired(true);
        $this->bodyPropertyDefinition = new PropertyDefinition($this->bodyProperty);
        $this->bodyPropertyDefinition->setObjectTypeDefinition($this->bodyParameterPropertyDefinition);

        $this->requestDefinition = new DtoDefinition([$this->pathPropertyDefinition, $this->bodyPropertyDefinition]);
    }

    public function tearDown(): void
    {
        unset($this->specificationLoader);
        parent::tearDown();
    }

    public function testGenerateClassGraph(): void
    {
        $openApiSpecificationArray =
        [
            'paths' => [
                '/goods/{goodId}' => [
                    'get' => [
                        'operationId' => 'getGood',
                        'parameters' => [
                            ['$ref' => '#/components/parameters/GoodIdParam'],
                        ],
                        'requestBody' => [
                            'required' => true,
                            'content' => [
                                'application/json' => [
                                    'schema' => ['$ref' => '#/components/schemas/GoodRequestSchema'],
                                ],
                            ],
                        ],
                        'responses' => [
                            '200' => [
                                'description' => 'OK',
                                'content' => [
                                    'application/json' => [
                                        'schema' => ['$ref' => '#/components/schemas/GoodResponseSchema'],
                                    ],
                                ],
                            ],
                            '304' => [
                                'description' => 'redirect',
                                'content' => [
                                    'application/json' => [
                                        'schema' => ['$ref' => '#/components/schemas/RedirectResponseSchema'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'components' => [
                'schemas' => [
                    'GoodResponseSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'title' => ['type' => 'string'],
                            'object_property' => ['type' => 'object'],
                        ],
                        'required' => ['title','object_property'],
                    ],
                    'GoodRequestSchema' => [
                        'required' => true,
                        'type' => 'object',
                        'properties' => [
                            'id' => ['type' => 'string'],
                        ],
                    ],
                    'RedirectResponseSchema' => [
                        'type' => 'object',
                        'properties' => [],
                    ],
                ],
                'parameters' => [
                    'GoodIdParam' => [
                        'name' => 'goodId',
                        'in' => 'path',
                        'required' => true,
                        'schema' => ['type' => 'string'],
                    ],
                ],
            ],
        ];

        $openApi = new OpenApi($openApiSpecificationArray);
        $openApi->setReferenceContext(new ReferenceContext($openApi, self::SPECIFICATION_PATH));
        $openApi->resolveReferences();

        $specification = (new SpecificationParser(new ScalarTypesResolver(), []))->parseOpenApi(self::SPECIFICATION_NAME, $this->specificationConfig, $openApi);

        $this->specificationLoader->expects(self::once())
            ->method('list')
            ->willReturn([self::SPECIFICATION_NAME => $this->specificationConfig]);

        $this->specificationLoader
            ->expects(self::once())
            ->method('load')
            ->with(self::SPECIFICATION_NAME)
            ->willReturn($specification);

        $componentDefinitions = [];
        $componentSchemas     = $specification->getComponentSchemas();
        foreach ($componentSchemas as $name => $_objectSchema) {
            $componentDefinitions[] = new ComponentDefinition($name);
        }

        $responseTitleProperty = new Property('title');
        $responseTitleProperty
            ->setRequired(true)
            ->setScalarTypeId(0);

        $responseObjectProperty = new Property('object_property');
        $responseObjectProperty
            ->setRequired(true)
            ->setObjectTypeDefinition(
                new ObjectSchema([])
            );
        $responseObjectPropertyTypeDefinition = new PropertyDefinition($responseObjectProperty);
        $responseObjectPropertyTypeDefinition->setObjectTypeDefinition(
            new DtoDefinition([])
        );
        $responseDefinition         = new ResponseDefinition('200', new DtoDefinition([new PropertyDefinition($responseTitleProperty), $responseObjectPropertyTypeDefinition]));
        $redirectResponseDefinition = new ResponseDefinition('304', new DtoDefinition([]));
        $responses                  = [$responseDefinition, $redirectResponseDefinition];

        $singleHttpCode = null;
        if (count($responses) === 1 && $responses[0]->getResponseBody()->isEmpty()) {
            $singleHttpCode = $responses[0]->getStatusCode();
            $responses      = [];
        }

        $service = new RequestHandlerInterfaceDefinition(
            $this->requestDefinition,
            array_map(
                static fn (ResponseDefinition $response) => $response->getResponseBody(),
                $responses
            )
        );

        $operationDefinition = new OperationDefinition(
            '/goods/{goodId}',
            'get',
            'getGood',
            'test.getGood',
            null,
            $singleHttpCode,
            $this->requestDefinition,
            $responses,
            $service
        );

        $specificationDefinition = new SpecificationDefinition($this->specificationConfig, [$operationDefinition], $componentDefinitions);
        $expectedGraphDefinition = new GraphDefinition([$specificationDefinition], new ServiceSubscriberDefinition());

        $graphGenerator  = new GraphGenerator($this->specificationLoader);
        $graphDefinition = $graphGenerator->generateClassGraph();

        $generatedSpecification                            = $graphDefinition->getSpecifications()[0];
        $generatedSpecificationOperations                  = $generatedSpecification->getOperations();
        $generatedSpecificationOperationResponses          = $generatedSpecificationOperations[0]->getResponses();
        $generatedSpecificationOperationResponseStatusCode = $generatedSpecificationOperationResponses[0]->getStatusCode();

        Assert::assertEquals($expectedGraphDefinition, $graphDefinition);
        Assert::assertCount(2, $generatedSpecificationOperationResponses);
        Assert::assertFalse($generatedSpecificationOperationResponses[0]->getResponseBody()->isEmpty());
        Assert::assertSame('200', $generatedSpecificationOperationResponseStatusCode);
    }

    public function testGenerateClassGraphTwo(): void
    {
        $openApiSpecificationArray =
            [
                'paths' => [
                    '/goods/{goodId}' => [
                        'get' => [
                            'operationId' => 'getGood',
                            'parameters' => [
                                ['$ref' => '#/components/parameters/GoodIdParam'],
                            ],
                            'requestBody' => [
                                'required' => true,
                                'content' => [
                                    'application/json' => [
                                        'schema' => ['$ref' => '#/components/schemas/GoodRequestSchema'],
                                    ],
                                ],
                            ],
                            'responses' => [
                                '200' => [
                                    'description' => 'OK',
                                    'content' => [
                                        'application/json' => [
                                            'schema' => ['$ref' => '#/components/schemas/GoodResponseSchema'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'components' => [
                    'schemas' => [
                        'GoodResponseSchema' => [
                            'type' => 'object',
                            'properties' => [],
                        ],
                        'GoodRequestSchema' => [
                            'required' => true,
                            'type' => 'object',
                            'properties' => [
                                'id' => ['type' => 'string'],
                            ],
                        ],
                    ],
                    'parameters' => [
                        'GoodIdParam' => [
                            'name' => 'goodId',
                            'in' => 'path',
                            'required' => true,
                            'schema' => ['type' => 'string'],
                        ],
                    ],
                ],
            ];

        $openApi = new OpenApi($openApiSpecificationArray);
        $openApi->setReferenceContext(new ReferenceContext($openApi, self::SPECIFICATION_PATH));
        $openApi->resolveReferences();

        $specification = (new SpecificationParser(new ScalarTypesResolver(), []))->parseOpenApi(self::SPECIFICATION_NAME, $this->specificationConfig, $openApi);

        $this->specificationLoader->expects(self::once())
            ->method('list')
            ->willReturn([self::SPECIFICATION_NAME => $this->specificationConfig]);

        $this->specificationLoader
            ->expects(self::once())
            ->method('load')
            ->with(self::SPECIFICATION_NAME)
            ->willReturn($specification);

        $componentDefinitions = [];
        $componentSchemas     = $specification->getComponentSchemas();
        foreach ($componentSchemas as $name => $_objectSchema) {
            $componentDefinitions[] = new ComponentDefinition($name);
        }

        $responseTitleProperty = new Property('title');
        $responseTitleProperty
            ->setRequired(true)
            ->setScalarTypeId(0);

        $responseDefinition = new ResponseDefinition('200', new DtoDefinition([]));
        $responses          = [$responseDefinition];

        $singleHttpCode = null;
        if (count($responses) === 1 && $responses[0]->getResponseBody()->isEmpty()) {
            $singleHttpCode = $responses[0]->getStatusCode();
            $responses      = [];
        }

        $service = new RequestHandlerInterfaceDefinition(
            $this->requestDefinition,
            array_map(
                static fn (ResponseDefinition $response) => $response->getResponseBody(),
                $responses
            )
        );

        $operationDefinition = new OperationDefinition(
            '/goods/{goodId}',
            'get',
            'getGood',
            'test.getGood',
            null,
            $singleHttpCode,
            $this->requestDefinition,
            $responses,
            $service
        );

        $specificationDefinition = new SpecificationDefinition($this->specificationConfig, [$operationDefinition], $componentDefinitions);
        $expectedGraphDefinition = new GraphDefinition([$specificationDefinition], new ServiceSubscriberDefinition());

        $graphGenerator  = new GraphGenerator($this->specificationLoader);
        $graphDefinition = $graphGenerator->generateClassGraph();

        Assert::assertEquals($expectedGraphDefinition, $graphDefinition);
    }

    public function testGenerateClassGraphThree(): void
    {
        $openApiSpecificationArray =
            [
                'paths' => [
                    '/goods/{goodId}' => [
                        'get' => [
                            'operationId' => 'getGood',
                            'parameters' => [
                                ['$ref' => '#/components/parameters/GoodIdParam'],
                            ],
                            'requestBody' => [
                                'required' => true,
                                'content' => [
                                    'application/json' => [
                                        'schema' => ['$ref' => '#/components/schemas/GoodRequestSchema'],
                                    ],
                                ],
                            ],
                            'responses' => [
                                '200' => [
                                    'description' => 'OK',
                                    'content' => [
                                        'application/json' => [
                                            'schema' => ['$ref' => '#/components/schemas/GoodResponseSchema'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'components' => [
                    'schemas' => [
                        'GoodResponseSchema' => [
                            'type' => 'object',
                            'properties' => [
                                'title' => ['type' => 'string'],
                                'object_property' => ['type' => 'object'],
                            ],
                            'required' => ['title','object_property'],
                        ],
                        'GoodRequestSchema' => [
                            'required' => true,
                            'type' => 'object',
                            'properties' => [
                                'id' => ['type' => 'string'],
                            ],
                        ],
                    ],
                    'parameters' => [
                        'GoodIdParam' => [
                            'name' => 'goodId',
                            'in' => 'path',
                            'required' => true,
                            'schema' => ['type' => 'string'],
                        ],
                    ],
                ],
            ];

        $openApi = new OpenApi($openApiSpecificationArray);
        $openApi->setReferenceContext(new ReferenceContext($openApi, self::SPECIFICATION_PATH));
        $openApi->resolveReferences();

        $specification = (new SpecificationParser(new ScalarTypesResolver(), []))->parseOpenApi(self::SPECIFICATION_NAME, $this->specificationConfig, $openApi);

        $this->specificationLoader->expects(self::once())
            ->method('list')
            ->willReturn([self::SPECIFICATION_NAME => $this->specificationConfig]);

        $this->specificationLoader
            ->expects(self::once())
            ->method('load')
            ->with(self::SPECIFICATION_NAME)
            ->willReturn($specification);

        $componentDefinitions = [];
        $componentSchemas     = $specification->getComponentSchemas();
        foreach ($componentSchemas as $name => $_objectSchema) {
            $componentDefinitions[] = new ComponentDefinition($name);
        }

        $responseTitleProperty = new Property('title');
        $responseTitleProperty
            ->setRequired(true)
            ->setScalarTypeId(0);

        $responseObjectProperty = new Property('object_property');
        $responseObjectProperty
            ->setRequired(true)
            ->setObjectTypeDefinition(
                new ObjectSchema([])
            );
        $responseObjectPropertyTypeDefinition = new PropertyDefinition($responseObjectProperty);
        $responseObjectPropertyTypeDefinition->setObjectTypeDefinition(
            new DtoDefinition([])
        );
        $responseDefinition = new ResponseDefinition('200', new DtoDefinition([new PropertyDefinition($responseTitleProperty), $responseObjectPropertyTypeDefinition]));
        $responses          = [$responseDefinition];

        $singleHttpCode = null;
        if (count($responses) === 1 && $responses[0]->getResponseBody()->isEmpty()) {
            $singleHttpCode = $responses[0]->getStatusCode();
            $responses      = [];
        }

        $service = new RequestHandlerInterfaceDefinition(
            $this->requestDefinition,
            array_map(
                static fn (ResponseDefinition $response) => $response->getResponseBody(),
                $responses
            )
        );

        $operationDefinition = new OperationDefinition(
            '/goods/{goodId}',
            'get',
            'getGood',
            'test.getGood',
            null,
            $singleHttpCode,
            $this->requestDefinition,
            $responses,
            $service
        );

        $specificationDefinition = new SpecificationDefinition($this->specificationConfig, [$operationDefinition], $componentDefinitions);
        $expectedGraphDefinition = new GraphDefinition([$specificationDefinition], new ServiceSubscriberDefinition());

        $graphGenerator  = new GraphGenerator($this->specificationLoader);
        $graphDefinition = $graphGenerator->generateClassGraph();

        $generatedSpecification                            = $graphDefinition->getSpecifications()[0];
        $generatedSpecificationOperations                  = $generatedSpecification->getOperations();
        $generatedSpecificationOperationResponses          = $generatedSpecificationOperations[0]->getResponses();
        $generatedSpecificationOperationResponseStatusCode = $generatedSpecificationOperationResponses[0]->getStatusCode();

        Assert::assertEquals($expectedGraphDefinition, $graphDefinition);
        Assert::assertCount(1, $generatedSpecificationOperationResponses);
        Assert::assertFalse($generatedSpecificationOperationResponses[0]->getResponseBody()->isEmpty());
        Assert::assertSame('200', $generatedSpecificationOperationResponseStatusCode);
    }
}
