<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Unit\CodeGenerator;

use cebe\openapi\ReferenceContext;
use cebe\openapi\spec\OpenApi;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\GraphDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\OperationDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\PropertyDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\RequestDtoDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\RequestParametersDtoDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\ResponseDtoDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\ServiceSubscriberDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\SpecificationDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\GraphGenerator;
use OnMoon\OpenApiServerBundle\Specification\Definitions\Property;
use OnMoon\OpenApiServerBundle\Specification\Definitions\SpecificationConfig;
use OnMoon\OpenApiServerBundle\Specification\SpecificationLoader;
use OnMoon\OpenApiServerBundle\Specification\SpecificationParser;
use OnMoon\OpenApiServerBundle\Types\ScalarTypesResolver;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

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
            'openapi' => '3.0.3',
            'paths' => [
                '/goods/{goodId}' => [
                    'get' => [
                        'operationId' => 'getGood',
                        'parameters' => [
                            ['$ref' => '#/components/parameters/GoodIdParam'],
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
                        ],
                        'required' => ['title'],
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

        $specification = (new SpecificationParser(new ScalarTypesResolver()))->parseOpenApi($specificationName, $specificationConfig, $openApi);

        $this->specificationLoader->expects(self::once())
            ->method('list')
            ->willReturn([$specificationName => $specificationConfig]);

        $this->specificationLoader
            ->expects(self::once())
            ->method('load')
            ->with($specificationName)
            ->willReturn($specification);

        $requestProperty = new Property('goodId');
        $requestProperty
            ->setRequired(true)
            ->setScalarTypeId(0);
        $requestParametersDtoDefinition = new RequestParametersDtoDefinition([new PropertyDefinition($requestProperty)]);
        $requestDtoDefinition           = new RequestDtoDefinition(null, null, $requestParametersDtoDefinition);

        $responseProperty = new Property('title');
        $responseProperty
            ->setRequired(true)
            ->setScalarTypeId(0);
        $responseDtoDefinition         = new ResponseDtoDefinition('200', [new PropertyDefinition($responseProperty)]);
        $redirectResponseDtoDefinition = new ResponseDtoDefinition('304', []);

        $operationDefinition     = new OperationDefinition('/goods/{goodId}', 'get', 'getGood', 'test.getGood', null, $requestDtoDefinition, [$responseDtoDefinition, $redirectResponseDtoDefinition]);
        $specificationDefinition = new SpecificationDefinition($specificationConfig, [$operationDefinition]);
        $expectedGraphDefinition = new GraphDefinition([$specificationDefinition], new ServiceSubscriberDefinition());

        $graphGenerator  = new GraphGenerator($this->specificationLoader);
        $graphDefinition = $graphGenerator->generateClassGraph();

        Assert::assertEquals($expectedGraphDefinition, $graphDefinition);
    }
}
