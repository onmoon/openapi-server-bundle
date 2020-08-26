<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Unit\CodeGenerator;

use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\DtoDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\PropertyDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\PhpParserGenerators\DtoCodeGenerator;
use OnMoon\OpenApiServerBundle\Specification\Definitions\Property;
use OnMoon\OpenApiServerBundle\Types\ScalarTypesResolver;
use PhpParser\BuilderFactory;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OnMoon\OpenApiServerBundle\CodeGenerator\PhpParserGenerators\DtoCodeGenerator
 */
final class DtoCodeGeneratorTest extends TestCase
{
    /**
     * @return mixed[]
     */
    public function generateProvider(): array
    {
        $basicDefinition = [
            'namespace' => 'Some\\Custom\\Namespace',
            'class' => 'SomeCustomClass',
            'properties' => [],
        ];

        $extendedDefinition = [
            'namespace' => 'Some\\Custom\\Namespace',
            'class' => 'SomeCustomClass',
            'properties' => [
                [
                    'classPropertyName' => 'name100',
                    'name' => 'name-100',
                    'defaultValue' => 'default-value-100',
                    'description' => 'Description of property "name-100"',
                    'required' => false,
                    'pattern' => null,
                    'array' => false,
                    'nullable' => false,
                    'scalarTypeId' => 8,
                ],
                [
                    'classPropertyName' => 'name300',
                    'name' => 'name-300',
                    'defaultValue' => 'default-value-300',
                    'description' => 'Description of property "name-300"',
                    'required' => true,
                    'pattern' => '',
                    'array' => true,
                    'nullable' => true,
                    'scalarTypeId' => 10,
                ],
                [
                    'classPropertyName' => 'name500',
                    'name' => '500',
                    'description' => 'Description of property "name-500"',
                    'required' => false,
                    'definition' => [
                        'namespace' => 'Some\\Custom\\Namespace50000',
                        'class' => 'SomeCustomClass50000',
                        'properties' => [
                            [
                                'classPropertyName' => 'name50100',
                                'name' => 'name-50100',
                                'defaultValue' => 'value-50100',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        return [
//            [
//                'payload' => [
//                    'definition' => $basicDefinition,
//                    'codeGenerator' => [
//                        'languageLevel' => 'undefined',
//                        'fullDocs' => false,
//                    ],
//                ],
//            ],
//            [
//                'payload' => [
//                    'definition' => $basicDefinition,
//                    'codeGenerator' => [
//                        'languageLevel' => '7.4',
//                        'fullDocs' => false,
//                    ],
//                ],
//            ],
//            [
//                'payload' => [
//                    'definition' => $basicDefinition,
//                    'codeGenerator' => [
//                        'languageLevel' => '7.4',
//                        'fullDocs' => true,
//                    ],
//                ],
//            ],
            [
                'payload' => [
                    'definition' => $extendedDefinition,
                    'codeGenerator' => [
                        'languageLevel' => '7.4',
                        'fullDocs' => false,
                    ],
                ],
            ],
//            [
//                'payload' => [
//                    'definition' => $extendedDefinition,
//                    'codeGenerator' => [
//                        'languageLevel' => '7.4',
//                        'fullDocs' => true,
//                    ],
//                ],
//            ],
        ];
    }

    /**
     * @param mixed[] $payload
     *
     * @dataProvider generateProvider
     */
    public function testGenerate(array $payload): void
    {
        $codeGenerator = new DtoCodeGenerator(
            new BuilderFactory(),
            new ScalarTypesResolver(),
            $payload['codeGenerator']['languageLevel'],
            $payload['codeGenerator']['fullDocs'],
        );

        $definition = $this->createDtoDefinition($payload['definition']);

        $generatedFileDefinition = $codeGenerator->generate($definition);

        Assert::assertSame($definition, $generatedFileDefinition->getClass());

        $this->assertMatchesRegularExpression(
            addslashes(sprintf(
                '/namespace %s;/',
                $payload['definition']['namespace']
            )),
            $generatedFileDefinition->getFileContents()
        );

        $this->assertMatchesRegularExpression(
            addslashes(sprintf(
                '/final class %s/',
                $payload['definition']['class']
            )),
            $generatedFileDefinition->getFileContents()
        );

        foreach ($payload['definition']['properties'] as $definitionProperty) {
            if (array_key_exists('description', $definitionProperty)) {
                $this->assertMatchesRegularExpression(
                    addslashes(sprintf(
                        '/\* %s/',
                        $definitionProperty['description']
                    )),
                    $generatedFileDefinition->getFileContents()
                );
            }
            if ($definitionProperty['codeGenerator']['fullDocs'] === true) {
                $this->assertMatchesRegularExpression(
                    addslashes(sprintf(
                        '/\* %s/',
                        $definitionProperty['description']
                    )),
                    $generatedFileDefinition->getFileContents()
                );
            }

            if (array_key_exists('definition', $definitionProperty)) {
                $this->assertMatchesRegularExpression(
                    addslashes(sprintf(
                        '/use %s\%s;/',
                        $definitionProperty['definition']['namespace'],
                        $definitionProperty['definition']['class']
                    )),
                    $generatedFileDefinition->getFileContents()
                );
            }
        }

        dump(
//            $generatedFileDefinition,
//            $generatedFileDefinition->getClass(),
            $generatedFileDefinition->getFileContents()
        );
        exit;
    }

    /**
     * @param mixed[] $payload
     */
    private function createDtoDefinition(array $payload): DtoDefinition
    {
        $definitionProperties = [];

        foreach ($payload['properties'] as $property) {
            $propertyDefinition = (new PropertyDefinition(
                (new Property($property['name']))
                    ->setDefaultValue($property['defaultValue'] ?? null)
                    ->setDescription($property['description'] ?? null)
                    ->setRequired($property['required'] ?? false)
                    ->setPattern($property['pattern'] ?? null)
                    ->setArray($property['array'] ?? false)
                    ->setNullable($property['nullable'] ?? false)
                    ->setScalarTypeId($property['scalarTypeId'] ?? null)
            ))->setClassPropertyName($property['classPropertyName']);

            if (array_key_exists('definition', $property)) {
                $subDefinition = $this->createDtoDefinition($property['definition']);
                $propertyDefinition->setObjectTypeDefinition($subDefinition);
            }

            $definitionProperties[] = $propertyDefinition;
        }

        return (new DtoDefinition($definitionProperties))
            ->setNamespace($payload['namespace'])
            ->setClassName($payload['class']);
    }
}
