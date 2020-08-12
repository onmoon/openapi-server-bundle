<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Unit\CodeGenerator;

use Lukasoppermann\Httpstatus\Httpstatus;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\DtoDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\GeneratedInterfaceDefinition;
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
use sspat\ReservedWords\ReservedWords;

use function array_map;
use function array_merge;
use function count;
use function str_replace;

use const DIRECTORY_SEPARATOR;

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
            'rootPath' => str_replace(['/', '\\'], DIRECTORY_SEPARATOR, '/Some/Custom/Path'),
            'namingStrategy' => [
                'rootNamespace' => 'Some\\Custom\\Root\\Namespace',
                'languageLevel' => 'some-custom-language-level',
            ],
            'httpStatus' => [
                'statusArray' => [200 => 'OK'],
            ],
        ];
    }

    /**
     * @return mixed[]
     */
    public function setAllNamesAndPathsProvider(): array
    {
        return [
            [
                'payload' => array_merge(
                    $this->getCommonPayload(),
                    [
                        'graph' => [
                            'specifications' => [],
                        ],
                    ]
                ),
            ],
            [
                'payload' => array_merge(
                    $this->getCommonPayload(),
                    [
                        'graph' => [
                            'specifications' => [
                                [
                                    'specificationConfig' => [
                                        'path' => str_replace(['/', '\\'], DIRECTORY_SEPARATOR, '/some/custom/specification/path'),
                                        'type' => null,
                                        'namespace' => 'Some\\Custom\\Namespace',
                                        'mediaType' => 'custom/media-type',
                                    ],
                                    'operations' => [],
                                ],
                            ],
                        ],
                    ]
                ),
            ],
            [
                'payload' => array_merge(
                    $this->getCommonPayload(),
                    [
                        'graph' => [
                            'specifications' => [
                                [
                                    'specificationConfig' => [
                                        'path' => str_replace(['/', '\\'], DIRECTORY_SEPARATOR, '/some/custom/specification/path'),
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
                                            'summary' => null,
                                            'request' => null,
                                            'responses' => [],
                                            'expected' => [
                                                'requestHandlerInterface' => [
                                                    'methodName' => 'someCustomOperationId',
                                                    'methodDescription' => null,
                                                    'fileName' => 'SomeCustomOperationId.php',
                                                    'filePath' => str_replace(['/', '\\'], DIRECTORY_SEPARATOR, '/Some/Custom/Path/Apis/SomeCustomNamespace/SomeCustomOperationId'),
                                                    'namespace' => 'Some\Custom\Namespace\Apis\SomeCustomNamespace\SomeCustomOperationId',
                                                    'className' => 'SomeCustomOperationId',
                                                ],
                                            ],
                                            'hasMakersInterface' => false,
                                        ],
                                        [
                                            'url' => 'http://example.local',
                                            'method' => 'GET',
                                            'operationId' => 'someCustomOperationId',
                                            'requestHandlerName' => 'someCustomRequestHandlerName',
                                            'summary' => 'SomeCustomSummary',
                                            'request' => [
                                                'body' => [
                                                    'properties' => [
                                                        [
                                                            'name' => 'someCustomRequestSpecProperty',
                                                            'requestNames' => [
                                                                'fileName' => 'BodyDto.php',
                                                                'filePath' => str_replace(['/', '\\'], DIRECTORY_SEPARATOR, '/Some/Custom/Path/Apis/SomeCustomNamespace/SomeCustomOperationId/Dto/Request/Body'),
                                                                'className' => 'BodyDto',
                                                                'namespace' => 'Some\Custom\Namespace\Apis\SomeCustomNamespace\SomeCustomOperationId\Dto\Request\Body',
                                                            ],
                                                            'classPropertyName' => 'someCustomRequestSpecProperty',
                                                            'getter' => 'getSomeCustomRequestSpecProperty',
                                                            'setter' => 'setSomeCustomRequestSpecProperty',
                                                        ],
                                                    ],
                                                    'requestNames' => [
                                                        'fileName' => 'SomeCustomOperationIdRequestDto.php',
                                                        'filePath' => str_replace(['/', '\\'], DIRECTORY_SEPARATOR, '/Some/Custom/Path/Apis/SomeCustomNamespace/SomeCustomOperationId/Dto/Request'),
                                                        'className' => 'SomeCustomOperationIdRequestDto',
                                                        'namespace' => 'Some\Custom\Namespace\Apis\SomeCustomNamespace\SomeCustomOperationId\Dto\Request',
                                                    ],
                                                    'classPropertyName' => 'body',
                                                    'getter' => 'getBody',
                                                    'setter' => 'setBody',
                                                ],
                                            ],
                                            'responses' => [
                                                [
                                                    'statusCode' => '200',
                                                    'properties' => [
                                                        [
                                                            'name' => 'SomeCustomResponseSpecProperty',
                                                            'classPropertyName' => 'SomeCustomResponseSpecProperty',
                                                            'getter' => 'getSomeCustomResponseSpecProperty',
                                                            'setter' => 'setSomeCustomResponseSpecProperty',
                                                        ],
                                                    ],
                                                    'responseNames' => [
                                                        'fileName' => 'SomeCustomOperationIdOKDto.php',
                                                        'filePath' => str_replace(['/', '\\'], DIRECTORY_SEPARATOR, '/Some/Custom/Path/Apis/SomeCustomNamespace/SomeCustomOperationId/Dto/Response/OK'),
                                                        'className' => 'SomeCustomOperationIdOKDto',
                                                        'namespace' => 'Some\Custom\Namespace\Apis\SomeCustomNamespace\SomeCustomOperationId\Dto\Response\OK',
                                                    ],

                                                    'responseMarkersInterfaceNames' => [
                                                        'extends' => null,
                                                        'fileName' => 'SomeCustomOperationIdResponse.php',
                                                        'filePath' => str_replace(['/', '\\'], DIRECTORY_SEPARATOR, '/Some/Custom/Path/Apis/SomeCustomNamespace/SomeCustomOperationId/Dto/Response'),
                                                        'className' => 'SomeCustomOperationIdResponse',
                                                        'namespace' => 'Some\Custom\Namespace\Apis\SomeCustomNamespace\SomeCustomOperationId\Dto\Response',
                                                    ],
                                                ],
                                            ],
                                            'expected' => [
                                                'requestHandlerInterface' => [
                                                    'methodName' => 'someCustomOperationId',
                                                    'methodDescription' => 'SomeCustomSummary',
                                                    'fileName' => 'SomeCustomOperationId.php',
                                                    'filePath' => str_replace(['/', '\\'], DIRECTORY_SEPARATOR, '/Some/Custom/Path/Apis/SomeCustomNamespace/SomeCustomOperationId'),
                                                    'namespace' => 'Some\Custom\Namespace\Apis\SomeCustomNamespace\SomeCustomOperationId',
                                                    'className' => 'SomeCustomOperationId',
                                                ],
                                            ],
                                            'hasMakersInterface' => true,
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

        /** @var SpecificationDefinition[]|array $specifications */
        $specifications = array_map(static function (array $payload): SpecificationDefinition {
            $specificationConfig = new SpecificationConfig(
                $payload['specificationConfig']['path'],
                $payload['specificationConfig']['type'],
                $payload['specificationConfig']['namespace'],
                $payload['specificationConfig']['mediaType']
            );

            /** @var OperationDefinition[]|array $operations */
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

                /** @var ResponseDtoDefinition[]|array $responses */
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

                $operationDefinition->setMarkersInterface(
                    (bool) $payload['hasMakersInterface'] ? new GeneratedInterfaceDefinition() : null
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
            str_replace(['/', '\\'], DIRECTORY_SEPARATOR, '/Some/Custom/Path/ServiceSubscriber'),
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
                            $operationDefinitionPayload['request']['body']['classPropertyName'],
                            $requestProperties[0]->getClassPropertyName()
                        );
                        Assert::assertSame(
                            $operationDefinitionPayload['request']['body']['getter'],
                            $requestProperties[0]->getGetterName()
                        );
                        Assert::assertSame(
                            $operationDefinitionPayload['request']['body']['setter'],
                            $requestProperties[0]->getSetterName()
                        );

                        $requestBody = $requestProperties[0]->getObjectTypeDefinition();
                        if ($requestBody !== null) {
                            Assert::assertSame(
                                $operationDefinitionPayload['request']['body']['properties'][0]['classPropertyName'],
                                $requestBody->getProperties()[0]->getClassPropertyName()
                            );
                            Assert::assertSame(
                                $operationDefinitionPayload['request']['body']['properties'][0]['getter'],
                                $requestBody->getProperties()[0]->getGetterName()
                            );
                            Assert::assertSame(
                                $operationDefinitionPayload['request']['body']['properties'][0]['setter'],
                                $requestBody->getProperties()[0]->getSetterName()
                            );
                        }
                    }
                }

                foreach ($operationDefinition->getResponses() as $operationResponseIndex => $operationDefinitionResponse) {
                    Assert::assertSame(
                        $operationDefinitionPayload['responses'][0]['responseNames']['fileName'],
                        $operationDefinitionResponse->getFileName()
                    );
                    Assert::assertSame(
                        $operationDefinitionPayload['responses'][0]['responseNames']['filePath'],
                        $operationDefinitionResponse->getFilePath()
                    );
                    Assert::assertSame(
                        $operationDefinitionPayload['responses'][0]['responseNames']['className'],
                        $operationDefinitionResponse->getClassName()
                    );
                    Assert::assertSame(
                        $operationDefinitionPayload['responses'][0]['responseNames']['namespace'],
                        $operationDefinitionResponse->getNamespace()
                    );

                    $responseProperties = $operationDefinitionResponse->getProperties();
                    if (count($responseProperties) <= 0) {
                        continue;
                    }

                    Assert::assertSame(
                        $operationDefinitionPayload['responses'][0]['properties'][0]['classPropertyName'],
                        $responseProperties[0]->getClassPropertyName()
                    );
                    Assert::assertSame(
                        $operationDefinitionPayload['responses'][0]['properties'][0]['getter'],
                        $responseProperties[0]->getGetterName()
                    );
                    Assert::assertSame(
                        $operationDefinitionPayload['responses'][0]['properties'][0]['setter'],
                        $responseProperties[0]->getSetterName()
                    );
                }

                if (! ($operationDefinition->getMarkersInterface() instanceof GeneratedInterfaceDefinition)) {
                    continue;
                }

                /** @var GeneratedInterfaceDefinition $markersInterface */
                $markersInterface = $operationDefinition->getMarkersInterface();

                Assert::assertSame(
                    $operationDefinitionPayload['responses'][0]['responseMarkersInterfaceNames']['fileName'],
                    $markersInterface->getFileName()
                );
                Assert::assertSame(
                    $operationDefinitionPayload['responses'][0]['responseMarkersInterfaceNames']['filePath'],
                    $markersInterface->getFilePath()
                );
                Assert::assertSame(
                    $operationDefinitionPayload['responses'][0]['responseMarkersInterfaceNames']['className'],
                    $markersInterface->getClassName()
                );
                Assert::assertSame(
                    $operationDefinitionPayload['responses'][0]['responseMarkersInterfaceNames']['namespace'],
                    $markersInterface->getNamespace()
                );
            }
        }
    }

    public function testSetRequestNames(): void
    {
        $payload = $this->getCommonPayload();

        $operationNamespace = 'Custom\Namespace';
        $operationName      = 'CustomName';
        $operationPath      = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, 'Custom/Path');

        $namingStrategy = new DefaultNamingStrategy(
            new ReservedWords(),
            $payload['namingStrategy']['rootNamespace'],
            $payload['namingStrategy']['languageLevel']
        );

        $httpStatus = new Httpstatus($payload['httpStatus']['statusArray']);

        $root = new RequestDtoDefinition();

        $nameGenerator = new NameGenerator(
            $namingStrategy,
            $httpStatus,
            $payload['rootNamespace'],
            $payload['rootPath']
        );
        $nameGenerator->setRequestNames(
            $root,
            $operationNamespace,
            $operationName,
            $operationPath
        );

        Assert::assertSame('CustomNameRequestDto.php', $root->getFileName());
        Assert::assertSame(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, 'Custom/Path/Dto/Request'), $root->getFilePath());
        Assert::assertSame('CustomNameRequestDto', $root->getClassName());
        Assert::assertSame('Custom\Namespace\Dto\Request', $root->getNamespace());
    }

    /**
     * @return mixed[]
     */
    public function setResponseNamesProvider(): array
    {
        return [
            [
                'additionalPayload' => ['statusCode' => '200'],
                'expected' => [
                    'fileName' => 'CustomClassNameOKDto.php',
                    'filePath' => str_replace(['/', '\\'], DIRECTORY_SEPARATOR, '/CustomPath/OK'),
                    'className' => 'CustomClassNameOKDto',
                    'namespace' => 'CustomNamespace\OK',
                ],
            ],
            [
                'additionalPayload' => ['statusCode' => 'BadStatusCode'],
                'expected' => [
                    'fileName' => 'CustomClassNameBadStatusCodeDto.php',
                    'filePath' => str_replace(['/', '\\'], DIRECTORY_SEPARATOR, '/CustomPath/BadStatusCode'),
                    'className' => 'CustomClassNameBadStatusCodeDto',
                    'namespace' => 'CustomNamespace\BadStatusCode',
                ],
            ],
        ];
    }

    /**
     * @param mixed[] $additionalPayload
     * @param mixed[] $expected
     *
     * @dataProvider setResponseNamesProvider
     */
    public function testSetResponseNames(array $additionalPayload, array $expected): void
    {
        $payload = $this->getCommonPayload();

        $namespace = '\CustomNamespace';
        $className = 'CustomClassName';
        $path      = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, '/CustomPath');

        $namingStrategy = new DefaultNamingStrategy(
            new ReservedWords(),
            $payload['namingStrategy']['rootNamespace'],
            $payload['namingStrategy']['languageLevel']
        );

        $httpStatus = new Httpstatus($payload['httpStatus']['statusArray']);

        $root = new ResponseDtoDefinition($additionalPayload['statusCode'], []);

        $nameGenerator = new NameGenerator(
            $namingStrategy,
            $httpStatus,
            $payload['rootNamespace'],
            $payload['rootPath']
        );
        $nameGenerator->setResponseNames(
            $root,
            $namespace,
            $className,
            $path
        );

        Assert::assertSame($expected['fileName'], $root->getFileName());
        Assert::assertSame($expected['filePath'], $root->getFilePath());
        Assert::assertSame($expected['className'], $root->getClassName());
        Assert::assertSame($expected['namespace'], $root->getNamespace());
    }

    public function testSetTreePathsAndClassNames(): void
    {
        $payload = $this->getCommonPayload();

        $namespace = '\CustomNamespace';
        $className = 'CustomClassName';
        $path      = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, '/CustomPath');

        $namingStrategy = new DefaultNamingStrategy(
            new ReservedWords(),
            $payload['namingStrategy']['rootNamespace'],
            $payload['namingStrategy']['languageLevel']
        );

        $httpStatus = new Httpstatus($payload['httpStatus']['statusArray']);

        $root = new DtoDefinition([
            new PropertyDefinition(
                new Property('someCustomMinorProperty')
            ),
            (new PropertyDefinition(
                new Property('someCustomProperty')
            ))->setObjectTypeDefinition(
                new DtoDefinition([
                    new PropertyDefinition(
                        new Property('someCustomSubProperty')
                    ),
                ])
            )->setClassPropertyName('someCustomClassProperty'),
        ]);

        $nameGenerator = new NameGenerator(
            $namingStrategy,
            $httpStatus,
            $payload['rootNamespace'],
            $payload['rootPath']
        );

        $nameGenerator->setTreePathsAndClassNames(
            $root,
            $namespace,
            $className,
            $path
        );

        Assert::assertSame('CustomClassName.php', $root->getFileName());
        Assert::assertSame(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, '/CustomPath'), $root->getFilePath());
        Assert::assertSame('CustomClassName', $root->getClassName());
        Assert::assertSame('\CustomNamespace', $root->getNamespace());

        $rootSubDefinition = $root->getProperties()[1]->getObjectTypeDefinition();
        if ($rootSubDefinition === null) {
            return;
        }

        Assert::assertSame('SomeCustomClassPropertyDto.php', $rootSubDefinition->getFileName());
        Assert::assertSame(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, '/CustomPath/SomeCustomClassProperty'), $rootSubDefinition->getFilePath());
        Assert::assertSame('SomeCustomClassPropertyDto', $rootSubDefinition->getClassName());
        Assert::assertSame('CustomNamespace\SomeCustomClassProperty', $rootSubDefinition->getNamespace());
    }

    public function testGetFileName(): void
    {
        $payload = $this->getCommonPayload();

        $className = 'CustomClassName';

        $namingStrategy = new DefaultNamingStrategy(
            new ReservedWords(),
            $payload['namingStrategy']['rootNamespace'],
            $payload['namingStrategy']['languageLevel']
        );

        $httpStatus = new Httpstatus($payload['httpStatus']['statusArray']);

        $nameGenerator = new NameGenerator(
            $namingStrategy,
            $httpStatus,
            $payload['rootNamespace'],
            $payload['rootPath']
        );

        Assert::assertSame($className . '.php', $nameGenerator->getFileName($className));
    }

    public function testSetTreeGettersSetters(): void
    {
        $payload = $this->getCommonPayload();

        $namingStrategy = new DefaultNamingStrategy(
            new ReservedWords(),
            $payload['namingStrategy']['rootNamespace'],
            $payload['namingStrategy']['languageLevel']
        );

        $httpStatus = new Httpstatus($payload['httpStatus']['statusArray']);

        $root = new DtoDefinition([
            (new PropertyDefinition(
                new Property('someCustomMinorProperty')
            ))->setClassPropertyName('someCustomMinorClassProperty'),
            (new PropertyDefinition(
                new Property('someCustomProperty')
            ))->setObjectTypeDefinition(
                new DtoDefinition([
                    (new PropertyDefinition(
                        new Property('someCustomSubProperty')
                    ))->setClassPropertyName('someCustomClassSubProperty'),
                ])
            )->setClassPropertyName('someCustomClassProperty'),
        ]);

        $nameGenerator = new NameGenerator(
            $namingStrategy,
            $httpStatus,
            $payload['rootNamespace'],
            $payload['rootPath']
        );

        $nameGenerator->setTreeGettersSetters($root);

        Assert::assertSame(
            'getSomeCustomMinorClassProperty',
            $root->getProperties()[0]->getGetterName()
        );
        Assert::assertSame(
            'setSomeCustomMinorClassProperty',
            $root->getProperties()[0]->getSetterName()
        );
        Assert::assertSame(
            'getSomeCustomClassProperty',
            $root->getProperties()[1]->getGetterName()
        );
        Assert::assertSame(
            'setSomeCustomClassProperty',
            $root->getProperties()[1]->getSetterName()
        );

        $subDefinition = $root->getProperties()[1]->getObjectTypeDefinition();
        if ($subDefinition === null) {
            return;
        }

        Assert::assertSame(
            'getSomeCustomClassSubProperty',
            $subDefinition->getProperties()[0]->getGetterName()
        );
        Assert::assertSame(
            'setSomeCustomClassSubProperty',
            $subDefinition->getProperties()[0]->getSetterName()
        );
    }

    public function testSetTreePropertyClassNames(): void
    {
        $payload = $this->getCommonPayload();

        $namingStrategy = new DefaultNamingStrategy(
            new ReservedWords(),
            $payload['namingStrategy']['rootNamespace'],
            $payload['namingStrategy']['languageLevel']
        );

        $httpStatus = new Httpstatus($payload['httpStatus']['statusArray']);

        $root = new DtoDefinition([
            new PropertyDefinition(
                new Property('someCustomMinorProperty')
            ),
            (new PropertyDefinition(
                new Property('someCustomProperty')
            ))->setObjectTypeDefinition(
                new DtoDefinition([
                    new PropertyDefinition(
                        new Property('someCustomSubProperty')
                    ),
                ])
            )->setClassPropertyName('someCustomClassProperty'),
            new PropertyDefinition(
                new Property('111')
            ),
        ]);

        $nameGenerator = new NameGenerator(
            $namingStrategy,
            $httpStatus,
            $payload['rootNamespace'],
            $payload['rootPath']
        );

        $nameGenerator->setTreePropertyClassNames($root);

        Assert::assertSame(
            'someCustomMinorProperty',
            $root->getProperties()[0]->getClassPropertyName()
        );
        Assert::assertSame(
            'someCustomProperty',
            $root->getProperties()[1]->getClassPropertyName()
        );

        $subDefinition = $root->getProperties()[1]->getObjectTypeDefinition();
        if ($subDefinition !== null) {
            Assert::assertSame(
                'someCustomSubProperty',
                $subDefinition->getProperties()[0]->getClassPropertyName()
            );
        }

        Assert::assertSame(
            '_111',
            $root->getProperties()[2]->getClassPropertyName()
        );
    }
}
