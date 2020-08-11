<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Unit\CodeGenerator\Definitions;

use Lukasoppermann\Httpstatus\Httpstatus;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\DtoDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\GraphDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\OperationDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\PropertyDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\RequestBodyDtoDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\RequestDtoDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\RequestHandlerInterfaceDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\ResponseDtoDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\ServiceSubscriberDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\SpecificationDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\NameGenerator;
use OnMoon\OpenApiServerBundle\CodeGenerator\Naming\DefaultNamingStrategy;
use OnMoon\OpenApiServerBundle\Specification\Definitions\Property;
use OnMoon\OpenApiServerBundle\Specification\Definitions\SpecificationConfig;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Safe\Exceptions\ArrayException;
use sspat\ReservedWords\ReservedWords;

use function array_map;
use function Safe\array_replace_recursive;

/**
 * @covers \OnMoon\OpenApiServerBundle\CodeGenerator\NameGenerator
 */
final class NameGeneratorTest extends TestCase
{
    /**
     * @return mixed[]
     */
    private function getCommonPayload(): array
    {
        return [
            'rootNamespace' => 'Some\\Custom\\Namespace',
            'rootPath' => '/Some/Custom/Path',
            'namingStrategy' => [
                'rootNamespace' => 'Some\\Custom\\Root\\Namespace',
                'languageLevel' => 'some-custom-language-level',
            ],
            'httpStatus' => [
                'statusArray' => [],
            ],
        ];
    }

    /**
     * @return mixed[]
     * @throws ArrayException
     */
    public function setAllNamesAndPathsProvider(): array
    {
//        $defaultPayload = array_replace_recursive(
//            $this->getCommonPayload(),
//            [
//                'graph' => [
//                    'specifications' => [],
//                ],
//            ]
//        );

        return [
//            [
//                'payload' => array_merge(
//                    $this->getCommonPayload(),
//                    [
//                        'graph' => [
//                            'specifications' => [],
//                        ],
//                    ]
//                )
//            ],
//            [
//                'payload' => array_merge(
//                    $this->getCommonPayload(),
//                    [
//                        'graph' => [
//                            'specifications' => [
//                                [
//                                    'specificationConfig' => [
//                                        'path' => '/some/custom/specification/path',
//                                        'type' => null,
//                                        'namespace' => 'Some\\Custom\\Namespace',
//                                        'mediaType' => 'custom/media-type',
//                                    ],
//                                    'operations' => [
//                                        [
//                                            'url' => 'http://example.local',
//                                            'method' => 'GET',
//                                            'operationId' => 'someCustomOperationId',
//                                            'requestHandlerName' => 'someCustomRequestHandlerName',
//                                            'summary' => null,
//                                            'request' => null,
//                                            'responses' => [],
//
//                                            'expected' => [
//                                                'requestHandlerInterface' => [
//                                                    'methodName' => 'someCustomOperationId',
//                                                    'methodDescription' => null,
//                                                    'fileName' => 'SomeCustomOperationId.php',
//                                                    'filePath' => '/Some/Custom/Path/Apis/SomeCustomNamespace/SomeCustomOperationId',
//                                                    'namespace' => 'Some\Custom\Namespace\Apis\SomeCustomNamespace\SomeCustomOperationId',
//                                                    'className' => 'SomeCustomOperationId',
//                                                ],
//                                            ],
//                                        ],
//                                    ],
//                                    'expected' => [
//
//                                    ],
//                                ],
//                            ],
//                        ],
//                    ]
//                ),
//            ],
            [
                'payload' => array_merge(
                    $this->getCommonPayload(),
                    [
                        'graph' => [
                            'specifications' => [
                                [
                                    'specificationConfig' => [
                                        'path' => '/some/custom/specification/path',
                                        'type' => 'some-custom-type',
                                        'namespace' => 'Some\\Custom\\Namespace',
                                        'mediaType' => 'custom/media-type',
                                    ],
                                    'operations' => [
                                        [
                                            'url' => 'http://example.local',
                                            'method' => 'GET',
                                            'operationId' => 'someCustomOperationId',
                                            'requestHandlerName' => 'someCustomRequestHandlerName',
                                            'summary' => 'SomeCustomSummary',
                                            'request' => [
                                                'body' => [
                                                    'properties' => [
                                                        'someCustomSpecProperty' => [
                                                            'getter' => 'getSomeCustomSpecProperty',
                                                            'setter' => 'setSomeCustomSpecProperty',
                                                        ],
                                                    ],
                                                    'requestNames' => [
                                                        'fileName' => 'SomeCustomOperationIdRequestDto.php',
                                                        'filePath' => '/Some/Custom/Path/Apis/SomeCustomNamespace/SomeCustomOperationId/Dto/Request',
                                                        'className' => 'SomeCustomOperationIdRequestDto',
                                                        'namespace' => 'Some\Custom\Namespace\Apis\SomeCustomNamespace\SomeCustomOperationId\Dto\Request',
                                                    ],
                                                ],
                                                'getter' => 'getBody',
                                                'setter' => 'setBody',
                                            ],
                                            'responses' => [
                                                [
                                                    'statusCode' => '200',
                                                    'properties' => [
                                                        'SomeCustomResponseProperty' => [
                                                            'getter' => 'getSomeCustomResponseProperty',
                                                            'setter' => 'setSomeCustomResponseProperty',
                                                        ]
                                                    ],
                                                    'responseNames' => [
                                                        'fileName' => 'SomeCustomOperationIdOKDto.php',
                                                        'filePath' => '/Some/Custom/Path/Apis/SomeCustomNamespace/SomeCustomOperationId/Dto/Response/OK',
                                                        'className' => 'SomeCustomOperationIdOKDto',
                                                        'namespace' => 'Some\Custom\Namespace\Apis\SomeCustomNamespace\SomeCustomOperationId\Dto\Response\OK',
                                                    ],
                                                ],
                                            ],

                                            'expected' => [
                                                'requestHandlerInterface' => [
                                                    'methodName' => 'someCustomOperationId',
                                                    'methodDescription' => 'SomeCustomSummary',
                                                    'fileName' => 'SomeCustomOperationId.php',
                                                    'filePath' => '/Some/Custom/Path/Apis/SomeCustomNamespace/SomeCustomOperationId',
                                                    'namespace' => 'Some\Custom\Namespace\Apis\SomeCustomNamespace\SomeCustomOperationId',
                                                    'className' => 'SomeCustomOperationId',
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ]
                ),
            ],
        ];
    }

    /**
     * @param mixed[] $payload
     *
     * @dataProvider setAllNamesAndPathsProvider
     */
    public function testSetAllNamesAndPaths(array $payload): void
    {
        $namingStrategy = new DefaultNamingStrategy(
            new ReservedWords(),
            $payload['namingStrategy']['rootNamespace'],
            $payload['namingStrategy']['languageLevel']
        );

        $httpStatus = new Httpstatus($payload['httpStatus']['statusArray']);

        /** @var SpecificationDefinition[] $specifications */
        $specifications = array_map(static function (array $payload): SpecificationDefinition {
            $specificationConfig = new SpecificationConfig(
                $payload['specificationConfig']['path'],
                $payload['specificationConfig']['type'],
                $payload['specificationConfig']['namespace'],
                $payload['specificationConfig']['mediaType']
            );

            /** @var OperationDefinition[] $operations */
            $operations = array_map(static function (array $payload): OperationDefinition {
                if ($payload['request'] !== null) {
                    $requestProperties = [];
                    if (count($payload['request']['body']['properties']) > 0) {
                        $requestProperties[] = new PropertyDefinition(
                            new Property($payload['request']['body']['properties'][0]['name'])
                        );
                    }

                    $request = new RequestDtoDefinition(
                        new RequestBodyDtoDefinition($requestProperties)
                    );
                } else {
                    $request = null;
                }

                /** @var ResponseDtoDefinition[] $responses */
                $responses = array_map(static function (array $payload): ResponseDtoDefinition {
                    $responseProperties = [];
                    if (count($payload['properties']) > 0) {
                        $responseProperties[] = new PropertyDefinition(
                            new Property($payload['properties'][0]['name'])
                        );
                    }

                    return new ResponseDtoDefinition(
                        $payload['statusCode'],
                        $responseProperties
                    );
                }, $payload['responses']);

                $operationDefinition = new OperationDefinition(
                    $payload['url'],
                    $payload['method'],
                    $payload['operationId'],
                    $payload['requestHandlerName'],
                    $payload['summary'],
                    $request,
                    $responses
                );

                $requestHandlerInterfaceDefinition = new RequestHandlerInterfaceDefinition();

                $operationDefinition->setRequestHandlerInterface($requestHandlerInterfaceDefinition);

                return $operationDefinition;
            }, $payload['operations']);

            return new SpecificationDefinition(
                $specificationConfig,
                $operations
            );
        }, $payload['graph']['specifications']);

        $serviceSubscriberDefinition = new ServiceSubscriberDefinition();

        $graphDefinition = new GraphDefinition(
            $specifications,
            $serviceSubscriberDefinition
        );

        $nameGenerator = new NameGenerator(
            $namingStrategy,
            $httpStatus,
            $payload['rootNamespace'],
            $payload['rootPath']
        );
        $nameGenerator->setAllNamesAndPaths($graphDefinition);

        Assert::assertSame(
            'ApiServiceLoaderServiceSubscriber.php',
            $graphDefinition->getServiceSubscriber()->getFileName()
        );
        Assert::assertSame(
            '/Some/Custom/Path/ServiceSubscriber',
            $graphDefinition->getServiceSubscriber()->getFilePath()
        );
        Assert::assertSame(
            'ApiServiceLoaderServiceSubscriber',
            $graphDefinition->getServiceSubscriber()->getClassName()
        );
        Assert::assertSame(
            'Some\\Custom\\Namespace\\ServiceSubscriber',
            $graphDefinition->getServiceSubscriber()->getNamespace()
        );

        Assert::assertSame($specifications, $graphDefinition->getSpecifications());

        foreach ($graphDefinition->getSpecifications() as $specDefIndex => $specificationDefinition) {
            foreach ($specificationDefinition->getOperations() as $operationDefIndex => $operationDefinition) {
//                dump(
//                    $operationDefinition->getResponses()[0]->getProperties(),
//                    $operationDefinition->getResponses()[0]->getProperties()[0]->getGetterName(),
//
//                '============'
//
//                //,
//
//
////                    $operationDefinition->getRequestHandlerInterface()->getMethodName(),
////                    $operationDefinition->getRequestHandlerInterface()->getMethodDescription(),
////                    $operationDefinition->getRequestHandlerInterface()->getFileName(),
////                    $operationDefinition->getRequestHandlerInterface()->getFilePath(),
////                    $operationDefinition->getRequestHandlerInterface()->getNamespace(),
////                    $operationDefinition->getRequestHandlerInterface()->getClassName(),
//                );
//                exit;

                $operationDefinitionPayload = $payload['graph']['specifications'][$specDefIndex]['operations'][$operationDefIndex];

                Assert::assertSame(
                    $operationDefinitionPayload['expected']['requestHandlerInterface']['methodName'],
                    $operationDefinition->getRequestHandlerInterface()->getMethodName()
                );
                Assert::assertSame(
                    $operationDefinitionPayload['expected']['requestHandlerInterface']['methodDescription'],
                    $operationDefinition->getRequestHandlerInterface()->getMethodDescription()
                );
                Assert::assertSame(
                    $operationDefinitionPayload['expected']['requestHandlerInterface']['fileName'],
                    $operationDefinition->getRequestHandlerInterface()->getFileName()
                );
                Assert::assertSame(
                    $operationDefinitionPayload['expected']['requestHandlerInterface']['filePath'],
                    $operationDefinition->getRequestHandlerInterface()->getFilePath()
                );
                Assert::assertSame(
                    $operationDefinitionPayload['expected']['requestHandlerInterface']['namespace'],
                    $operationDefinition->getRequestHandlerInterface()->getNamespace()
                );
                Assert::assertSame(
                    $operationDefinitionPayload['expected']['requestHandlerInterface']['className'],
                    $operationDefinition->getRequestHandlerInterface()->getClassName()
                );

                if ($operationDefinition->getRequest() !== null) {
                    Assert::assertSame(
                        $operationDefinitionPayload['request']['body']['requestNames']['fileName'],
                        $operationDefinition->getRequest()->getFileName()
                    );
                    Assert::assertSame(
                        $operationDefinitionPayload['request']['body']['requestNames']['filePath'],
                        $operationDefinition->getRequest()->getFilePath()
                    );
                    Assert::assertSame(
                        $operationDefinitionPayload['request']['body']['requestNames']['className'],
                        $operationDefinition->getRequest()->getClassName()
                    );
                    Assert::assertSame(
                        $operationDefinitionPayload['request']['body']['requestNames']['namespace'],
                        $operationDefinition->getRequest()->getNamespace()
                    );

                    $requestProperties = $operationDefinition->getRequest()->getProperties();
                    if (count($requestProperties) > 0) {
                        Assert::assertSame(
                            $operationDefinitionPayload['request']['body']['properties'][0]['class'],
                            $requestProperties[0]->getClassPropertyName()
                        );
                        Assert::assertSame(
                            $operationDefinitionPayload['request']['body']['properties'][0]['getter'],
                            $requestProperties[0]->getGetterName()
                        );
                        Assert::assertSame(
                            $operationDefinitionPayload['request']['body']['properties'][0]['setter'],
                            $requestProperties[0]->getSetterName()
                        );
                    }
                }

                foreach ($operationDefinition->getResponses() as $operationResponseIndex => $operationDefinitionResponse) {
                    Assert::assertSame(
                        $operationDefinitionPayload['responses']['responseNames']['fileName'],
                        $operationDefinitionResponse->getFileName()
                    );
                    Assert::assertSame(
                        $operationDefinitionPayload['responses']['responseNames']['filePath'],
                        $operationDefinitionResponse->getFilePath()
                    );
                    Assert::assertSame(
                        $operationDefinitionPayload['responses']['responseNames']['className'],
                        $operationDefinitionResponse->getClassName()
                    );
                    Assert::assertSame(
                        $operationDefinitionPayload['responses']['responseNames']['namespace'],
                        $operationDefinitionResponse->getNamespace()
                    );
//
                    $responseProperties = $operationDefinitionResponse->getProperties();
                    if (count($responseProperties) > 0) {
                        Assert::assertSame(
                            $operationDefinitionPayload['responses']['properties'][0]['name'],
                            $responseProperties[0]->getClassPropertyName()
                        );
                        Assert::assertSame(
                            $operationDefinitionPayload['responses']['properties'][0]['getter'],
                            $responseProperties[0]->getGetterName()
                        );
                        Assert::assertSame(
                            $operationDefinitionPayload['responses']['properties'][0]['setter'],
                            $responseProperties[0]->getSetterName()
                        );
                    }
                }
            }
        }
    }

//    public function testSetRequestNames(): void
//    {
//        $payload = $this->getCommonPayload();
//
//        $operationNamespace = '';
//        $operationName = '';
//        $operationPath = '';
//
//        $namingStrategy = new DefaultNamingStrategy(
//            new ReservedWords(),
//            $payload['namingStrategy']['rootNamespace'],
//            $payload['namingStrategy']['languageLevel']
//        );
//
//        $httpStatus = new Httpstatus($payload['httpStatus']['statusArray']);
//
//        $requestDtoDefinition = new RequestDtoDefinition();
//
//        $nameGenerator = new NameGenerator(
//            $namingStrategy,
//            $httpStatus,
//            $payload['rootNamespace'],
//            $payload['rootPath']
//        );
//        $nameGenerator->setRequestNames(
//            $requestDtoDefinition,
//            $operationNamespace,
//            $operationName,
//            $operationPath
//        );
//    }
//
//    public function testSetResponseNames(): void
//    {
//        $payload = $this->getCommonPayload();
//
//        $responseNamespace = '';
//        $operationName = '';
//        $responsePath = '';
//
//        $namingStrategy = new DefaultNamingStrategy(
//            new ReservedWords(),
//            $payload['namingStrategy']['rootNamespace'],
//            $payload['namingStrategy']['languageLevel']
//        );
//
//        $httpStatus = new Httpstatus($payload['httpStatus']['statusArray']);
//
//        $response = new ResponseDtoDefinition('200', []);
//
//        $nameGenerator = new NameGenerator(
//            $namingStrategy,
//            $httpStatus,
//            $payload['rootNamespace'],
//            $payload['rootPath']
//        );
//        $nameGenerator->setResponseNames(
//            $response,
//            $responseNamespace,
//            $operationName,
//            $responsePath
//        );
//    }
//
//    public function testSetTreePathsAndClassNames(): void
//    {
//        $payload = $this->getCommonPayload();
//
//        $namingStrategy = new DefaultNamingStrategy(
//            new ReservedWords(),
//            $payload['namingStrategy']['rootNamespace'],
//            $payload['namingStrategy']['languageLevel']
//        );
//
//        $httpStatus = new Httpstatus($payload['httpStatus']['statusArray']);
//
//        $root = new ResponseDtoDefinition('200', []);
//        $namespace = '';
//        $className = '';
//        $path = '';
//
//        $nameGenerator = new NameGenerator(
//            $namingStrategy,
//            $httpStatus,
//            $payload['rootNamespace'],
//            $payload['rootPath']
//        );
//        $nameGenerator->setResponseNames(
//            $root,
//            $namespace,
//            $className,
//            $path
//        );
//    }
//
//    public function testGetFileName(): void
//    {
//        $payload = $this->getCommonPayload();
//
//        $namingStrategy = new DefaultNamingStrategy(
//            new ReservedWords(),
//            $payload['namingStrategy']['rootNamespace'],
//            $payload['namingStrategy']['languageLevel']
//        );
//
//        $httpStatus = new Httpstatus($payload['httpStatus']['statusArray']);
//
//        $className = '';
//
//        $nameGenerator = new NameGenerator(
//            $namingStrategy,
//            $httpStatus,
//            $payload['rootNamespace'],
//            $payload['rootPath']
//        );
//
//        $filename = $nameGenerator->getFileName($className);
//    }
//
//    public function testSetTreeGettersSetters(): void
//    {
//        $payload = $this->getCommonPayload();
//
//        $namingStrategy = new DefaultNamingStrategy(
//            new ReservedWords(),
//            $payload['namingStrategy']['rootNamespace'],
//            $payload['namingStrategy']['languageLevel']
//        );
//
//        $httpStatus = new Httpstatus($payload['httpStatus']['statusArray']);
//
//        $root = new DtoDefinition([]);
//
//        $nameGenerator = new NameGenerator(
//            $namingStrategy,
//            $httpStatus,
//            $payload['rootNamespace'],
//            $payload['rootPath']
//        );
//
//        $nameGenerator->setTreeGettersSetters($root);
//    }
//
//    public function testSetTreePropertyClassNames(): void
//    {
//        $payload = $this->getCommonPayload();
//
//        $namingStrategy = new DefaultNamingStrategy(
//            new ReservedWords(),
//            $payload['namingStrategy']['rootNamespace'],
//            $payload['namingStrategy']['languageLevel']
//        );
//
//        $httpStatus = new Httpstatus($payload['httpStatus']['statusArray']);
//
//        $root = new DtoDefinition([]);
//
//        $nameGenerator = new NameGenerator(
//            $namingStrategy,
//            $httpStatus,
//            $payload['rootNamespace'],
//            $payload['rootPath']
//        );
//
//        $nameGenerator->setTreePropertyClassNames($root);
//    }
}
