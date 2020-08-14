<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Unit\Specification;

use cebe\openapi\exceptions\TypeErrorException;
use cebe\openapi\spec\MediaType;
use cebe\openapi\spec\OpenApi;
use cebe\openapi\spec\Operation;
use cebe\openapi\spec\Parameter;
use cebe\openapi\spec\Paths;
use cebe\openapi\spec\Response;
use cebe\openapi\spec\Responses;
use cebe\openapi\spec\Schema;
use cebe\openapi\spec\Type;
use OnMoon\OpenApiServerBundle\Exception\CannotParseOpenApi;
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
    public function testParseOpenApiSuccessDefault(): void
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
                '/some/custom/first/url' => [
                    'get' => new Operation(['operationId' => 'SomeCustomFirstGetOperation']),
                ],
                '/some/custom/second/url' => [
                    'get' => new Operation(['operationId' => 'SomeCustomSecondGetOperation']),
                    'post' => new Operation(['operationId' => 'SomeCustomSecondPostOperation']),
                ],
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
                                            'properties' => [
                                                'someProperty' => new Schema([
                                                    'type' => Type::INTEGER,
                                                    'default' => 'SomeDefaultValue',
                                                    'readOnly' => true,
                                                    'writeOnly' => false,
                                                    'required' => true,
                                                ]),
                                            ],
                                            'required' => false,
                                        ]),
                                    ]),
                                ],
                            ]),
                        ]),
                        'parameters' => [
                            new Parameter([
                                'name' => 'firstParam',
                                'in' => 'query',
                                'schema' => new Schema([
                                    'type' => Type::INTEGER,
                                    'default' => 'SomeDefaultValue2',
                                ])
                            ]),
                            new Parameter([
                                'name' => 'firstParam',
                                'in' => 'path',
                                'schema' => new Schema([
                                    'type' => Type::INTEGER,
                                    'default' => 'SomeDefaultValue2',
                                ])
                            ]),
                        ]
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

        Assert::assertSame(
            '/some/custom/first/url',
            $specification->getOperation('SomeCustomFirstGetOperation')->getUrl()
        );
        Assert::assertSame(
            '/some/custom/second/url',
            $specification->getOperation('SomeCustomSecondGetOperation')->getUrl()
        );
        Assert::assertSame(
            '/some/custom/second/url',
            $specification->getOperation('SomeCustomSecondPostOperation')->getUrl()
        );
        Assert::assertSame(
            '/some/custom/third/url',
            $specification->getOperation('SomeCustomThirdPostOperation')->getUrl()
        );
        Assert::assertSame(
            'firstParam',
            $specification->getOperation('SomeCustomThirdPostOperation')->getRequestParameters()['path']->getProperties()[1]->getName()
        );
        Assert::assertSame(
            'firstParam',
            $specification->getOperation('SomeCustomThirdPostOperation')->getRequestParameters()['query']->getProperties()[0]->getName()
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
