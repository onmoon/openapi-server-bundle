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

/** @covers \OnMoon\OpenApiServerBundle\CodeGenerator\GraphGenerator */
class GraphGeneratorTest extends TestCase
{
    /** @var SpecificationLoader|MockObject  */
    private $specificationLoader;

    public function setUp(): void
    {
        $this->specificationLoader = $this->createMock(SpecificationLoader::class);
    }

    public function tearDown(): void
    {
        unset($this->specificationLoader);
        parent::tearDown();
    }

    public function testGenerateClassGraph(): void
    {
        $specificationPath         = '/';
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

        $specificationName   = 'test';
        $specificationConfig = new SpecificationConfig(
            $specificationPath,
            null,
            '/',
            'application/json'
        );

        $openApi = new OpenApi($openApiSpecificationArray);
        $openApi->setReferenceContext(new ReferenceContext($openApi, $specificationPath));
        $openApi->resolveReferences();

        $specification = (new SpecificationParser(new ScalarTypesResolver(), []))->parseOpenApi($specificationName, $specificationConfig, $openApi);

        $this->specificationLoader->expects(self::once())
            ->method('list')
            ->willReturn([$specificationName => $specificationConfig]);

        $this->specificationLoader
            ->expects(self::once())
            ->method('load')
            ->with($specificationName)
            ->willReturn($specification);

        $componentDefinitions = [];
        $componentSchemas     = $specification->getComponentSchemas();
        foreach ($componentSchemas as $name => $_objectSchema) {
            $componentDefinitions[] = new ComponentDefinition($name);
        }

        $pathParameterProperty = new Property('goodId');
        $pathParameterProperty
            ->setRequired(true)
            ->setScalarTypeId(0);
        $pathParameterPropertyDefinition = new DtoDefinition([new PropertyDefinition($pathParameterProperty)]);

        $pathProperty = new Property('pathParameters');
        $pathProperty->setRequired(true);
        $pathPropertyDefinition = new PropertyDefinition($pathProperty);
        $pathPropertyDefinition->setObjectTypeDefinition($pathParameterPropertyDefinition);

        $bodyParameterProperty = new Property('id');
        $bodyParameterProperty->setScalarTypeId(0);
        $bodyParameterPropertyDefinition = new DtoDefinition([new PropertyDefinition($bodyParameterProperty)]);

        $bodyProperty = new Property('body');
        $bodyProperty->setRequired(true);
        $bodyPropertyDefinition = new PropertyDefinition($bodyProperty);
        $bodyPropertyDefinition->setObjectTypeDefinition($bodyParameterPropertyDefinition);

        $requestDefinition = new DtoDefinition([$pathPropertyDefinition, $bodyPropertyDefinition]);

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

        $service = new RequestHandlerInterfaceDefinition(
            $requestDefinition,
            array_map(
                static fn (ResponseDefinition $response) => $response->getResponseBody(),
                [$responseDefinition, $redirectResponseDefinition]
            )
        );

        $operationDefinition = new OperationDefinition(
            '/goods/{goodId}',
            'get',
            'getGood',
            'test.getGood',
            null,
            null,
            $requestDefinition,
            [$responseDefinition, $redirectResponseDefinition],
            $service
        );

        $specificationDefinition = new SpecificationDefinition($specificationConfig, [$operationDefinition], $componentDefinitions);
        $expectedGraphDefinition = new GraphDefinition([$specificationDefinition], new ServiceSubscriberDefinition());

        $graphGenerator  = new GraphGenerator($this->specificationLoader);
        $graphDefinition = $graphGenerator->generateClassGraph();

        Assert::assertEquals($expectedGraphDefinition, $graphDefinition);
    }
}
