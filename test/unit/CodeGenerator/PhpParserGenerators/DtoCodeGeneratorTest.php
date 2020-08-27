<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Unit\CodeGenerator\PhpParserGenerators;

use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\DtoDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\PropertyDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\PhpParserGenerators\DtoCodeGenerator;
use OnMoon\OpenApiServerBundle\Specification\Definitions\Property;
use OnMoon\OpenApiServerBundle\Types\ScalarTypesResolver;
use PhpParser\BuilderFactory;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

use function array_key_exists;
use function ucfirst;

/**
 * @covers \OnMoon\OpenApiServerBundle\CodeGenerator\PhpParserGenerators\DtoCodeGenerator
 */
final class DtoCodeGeneratorTest extends TestCase
{
    private const DEFINITION_EMPTY_PROPERTIES = [
        'namespace' => 'Some\\Custom\\Namespace',
        'class' => 'SomeCustomClass',
        'properties' => [],
    ];

    private const DEFINITION_BASIC = [
        'namespace' => 'Some\\Custom\\Namespace',
        'class' => 'SomeCustomClass',
        'properties' => [
            [
                'classPropertyName' => 'name100',
                'isInConstructor' => false,
                'hasGetter' => false,
                'hasSetter' => false,
                'name' => 'name-100',
                'defaultValue' => 'default-value-100',
                'description' => null,
                'required' => false,
                'pattern' => '1111111111111111111111111111111111111111111111111111111',
                'array' => false,
                'nullable' => false,
                'scalarTypeId' => 8,
                'expected' => ['renderedParamPattern' => '/private [?]int [$]name100 = null;/'],
                'typeHint' => 'int',
            ],
            [
                'classPropertyName' => 'name300',
                'isInConstructor' => true,
                'hasGetter' => false,
                'hasSetter' => false,
                'name' => 'name-300',
                'defaultValue' => 'default-value-300',
                'description' => null,
                'required' => false,
                'pattern' => '1111111111111111111111111111111111111111111111111111111',
                'array' => false,
                'nullable' => false,
                'scalarTypeId' => 8,
                'expected' => ['renderedParamPattern' => '/private [?]int [$]name300 = null;/'],
                'typeHint' => 'int',
            ],
        ],
    ];

    private const DEFINITION_EXTENDED = [
        'namespace' => 'Some\\Custom\\Namespace',
        'class' => 'SomeCustomClass',
        'properties' => [
            [
                'classPropertyName' => 'name100',
                'isInConstructor' => false,
                'hasGetter' => false,
                'hasSetter' => false,
                'name' => 'name-100',
                'defaultValue' => 'default-value-100',
                'description' => 'Description of property "name-100"',
                'required' => false,
                'pattern' => '1111111111111111111111111111111111111111111111111111111',
                'array' => false,
                'nullable' => false,
                'scalarTypeId' => 8,
                'expected' => ['renderedParamPattern' => '/private [?]int [$]name100 = null;/'],
                'typeHint' => 'int',
            ],
            [
                'classPropertyName' => 'name300',
                'isInConstructor' => false,
                'hasGetter' => false,
                'hasSetter' => false,
                'name' => 'name-300',
                'defaultValue' => 'default-value-300',
                'description' => 'Description of property "name-300"',
                'required' => true,
                'pattern' => '',
                'array' => true,
                'nullable' => true,
                'scalarTypeId' => 10,
                'typeHint' => 'array',
            ],
            [
                'classPropertyName' => 'name500',
                'isInConstructor' => false,
                'hasGetter' => false,
                'hasSetter' => false,
                'name' => 'name-500',
                'description' => 'Description of property "name-500"',
                'required' => false,
                'definition' => [
                    'namespace' => 'Some\\Custom\\Namespace50000',
                    'class' => 'SomeCustomClass50000',
                    'properties' => [
                        [
                            'classPropertyName' => 'name50100',
                            'isInConstructor' => false,
                            'hasGetter' => false,
                            'hasSetter' => false,
                            'name' => 'name-50100',
                            'defaultValue' => 'value-50100',
                        ],
                    ],
                ],
                'typeHint' => 'SomeCustomClass50000',
            ],
            [
                'classPropertyName' => 'name700',
                'isInConstructor' => true,
                'hasGetter' => true,
                'hasSetter' => true,
                'name' => 'name-700',
                'defaultValue' => 'default-value-700',
                'description' => 'Description of property "name-700"',
                'required' => true,
                'pattern' => '',
                'array' => false,
                'nullable' => true,
                'scalarTypeId' => 10,
                'typeHint' => 'array',
            ],
            [
                'classPropertyName' => 'name800',
                'isInConstructor' => true,
                'hasGetter' => true,
                'hasSetter' => true,
                'name' => 'name-700',
                'defaultValue' => 'default-value-800',
                'description' => 'Description of property "name-800"',
                'required' => true,
                'pattern' => '',
                'array' => true,
                'nullable' => true,
                'scalarTypeId' => 10,
                'typeHint' => 'array',
            ],
        ],
    ];

    private const EXAMPLE_FILE_CONTENT_EMPTY_PROPERTIES_SHORT_DOC_7_4 = '<?php

declare (strict_types=1);
namespace Some\Custom\Namespace;

/**
 * This class was automatically generated
 * You should not change it manually as it will be overwritten
 */
final class SomeCustomClass
{
    /** @inheritDoc */
    public function toArray() : array
    {
        return array();
    }
    /** @inheritDoc */
    public static function fromArray(array $data) : self
    {
        return new SomeCustomClass();
    }
}';

    private const EXAMPLE_FILE_CONTENT_BASIC_SHORT_DOC_7_4 = '<?php

declare (strict_types=1);
namespace Some\Custom\Namespace;

/**
 * This class was automatically generated
 * You should not change it manually as it will be overwritten
 */
final class SomeCustomClass
{
    private ?int $name100 = null;
    private ?int $name300 = null;
    public function __construct(?int $name300)
    {
        $this->name300 = $name300;
    }
    /** @inheritDoc */
    public function toArray() : array
    {
        return array(\'name-100\' => $this->name100, \'name-300\' => $this->name300);
    }
    /** @inheritDoc */
    public static function fromArray(array $data) : self
    {
        $dto = new SomeCustomClass($data[\'name-300\']);
        $dto->name100 = $data[\'name-100\'];
        return $dto;
    }
}';

    private const EXAMPLE_FILE_CONTENT_EXTENDED_SHORT_DOC_7_4 = '<?php

declare (strict_types=1);
namespace Some\Custom\Namespace;

use Some\Custom\Namespace50000\SomeCustomClass50000;
/**
 * This class was automatically generated
 * You should not change it manually as it will be overwritten
 */
final class SomeCustomClass
{
    /**
     * Description of property "name-100"
     * 
     */
    private ?int $name100 = null;
    /**
     * Description of property "name-300"
     * 
     * @var int[]|null $name300
     */
    private ?array $name300 = null;
    /**
     * Description of property "name-500"
     * 
     */
    private ?SomeCustomClass50000 $name500 = null;
    /**
     * Description of property "name-700"
     * 
     */
    private ?int $name700 = null;
    /**
     * Description of property "name-800"
     * 
     * @var int[]|null $name800
     */
    private ?array $name800 = null;
    /** @param int[]|null $name800 */
    public function __construct(?int $name700, ?array $name800)
    {
        $this->name700 = $name700;
        $this->name800 = $name800;
    }
    public function getName700() : ?int
    {
        return $this->name700;
    }
    /** @return int[]|null */
    public function getName800() : ?array
    {
        return $this->name800;
    }
    public function setName700(?int $name700) : self
    {
        $this->name700 = $name700;
        return $this;
    }
    /** @param int[]|null $name800 */
    public function setName800(?array $name800) : self
    {
        $this->name800 = $name800;
        return $this;
    }
    /** @inheritDoc */
    public function toArray() : array
    {
        return array(\'name-100\' => $this->name100, \'name-300\' => $this->name300, \'name-500\' => null === $this->name500 ? null : $this->name500->toArray(), \'name-700\' => $this->name700, \'name-700\' => $this->name800);
    }
    /** @inheritDoc */
    public static function fromArray(array $data) : self
    {
        $dto = new SomeCustomClass($data[\'name-700\'], $data[\'name-700\']);
        $dto->name100 = $data[\'name-100\'];
        $dto->name300 = $data[\'name-300\'];
        $dto->name500 = null === $data[\'name-500\'] ? null : SomeCustomClass50000::fromArray($data[\'name-500\']);
        return $dto;
    }
}';

    private const EXAMPLE_FILE_CONTENT_EMPTY_PROPERTIES_FULL_DOC_7_4 = '<?php

declare (strict_types=1);
namespace Some\Custom\Namespace;

/**
 * This class was automatically generated
 * You should not change it manually as it will be overwritten
 */
final class SomeCustomClass
{
    /** @inheritDoc */
    public function toArray() : array
    {
        return array();
    }
    /** @inheritDoc */
    public static function fromArray(array $data) : self
    {
        return new SomeCustomClass();
    }
}';

    private const EXAMPLE_FILE_CONTENT_BASIC_FULL_DOC_7_4 = '<?php

declare (strict_types=1);
namespace Some\Custom\Namespace;

/**
 * This class was automatically generated
 * You should not change it manually as it will be overwritten
 */
final class SomeCustomClass
{
    /** @var int|null $name100 */
    private ?int $name100 = null;
    /** @var int|null $name300 */
    private ?int $name300 = null;
    /** @param int|null $name300 */
    public function __construct(?int $name300)
    {
        $this->name300 = $name300;
    }
    /** @inheritDoc */
    public function toArray() : array
    {
        return array(\'name-100\' => $this->name100, \'name-300\' => $this->name300);
    }
    /** @inheritDoc */
    public static function fromArray(array $data) : self
    {
        $dto = new SomeCustomClass($data[\'name-300\']);
        $dto->name100 = $data[\'name-100\'];
        return $dto;
    }
}';

    private const EXAMPLE_FILE_CONTENT_EXTENDED_FULL_DOC_7_4 = '<?php

declare (strict_types=1);
namespace Some\Custom\Namespace;

use Some\Custom\Namespace50000\SomeCustomClass50000;
/**
 * This class was automatically generated
 * You should not change it manually as it will be overwritten
 */
final class SomeCustomClass
{
    /**
     * Description of property "name-100"
     * 
     * @var int|null $name100
     */
    private ?int $name100 = null;
    /**
     * Description of property "name-300"
     * 
     * @var int[]|null $name300
     */
    private ?array $name300 = null;
    /**
     * Description of property "name-500"
     * 
     * @var SomeCustomClass50000|null $name500
     */
    private ?SomeCustomClass50000 $name500 = null;
    /**
     * Description of property "name-700"
     * 
     * @var int|null $name700
     */
    private ?int $name700 = null;
    /**
     * Description of property "name-800"
     * 
     * @var int[]|null $name800
     */
    private ?array $name800 = null;
    /**
     * @param int|null $name700
     * @param int[]|null $name800
     */
    public function __construct(?int $name700, ?array $name800)
    {
        $this->name700 = $name700;
        $this->name800 = $name800;
    }
    /** @return int|null */
    public function getName700() : ?int
    {
        return $this->name700;
    }
    /** @return int[]|null */
    public function getName800() : ?array
    {
        return $this->name800;
    }
    /**
     * @param int|null $name700
     * @return self
     */
    public function setName700(?int $name700) : self
    {
        $this->name700 = $name700;
        return $this;
    }
    /**
     * @param int[]|null $name800
     * @return self
     */
    public function setName800(?array $name800) : self
    {
        $this->name800 = $name800;
        return $this;
    }
    /** @inheritDoc */
    public function toArray() : array
    {
        return array(\'name-100\' => $this->name100, \'name-300\' => $this->name300, \'name-500\' => null === $this->name500 ? null : $this->name500->toArray(), \'name-700\' => $this->name700, \'name-700\' => $this->name800);
    }
    /** @inheritDoc */
    public static function fromArray(array $data) : self
    {
        $dto = new SomeCustomClass($data[\'name-700\'], $data[\'name-700\']);
        $dto->name100 = $data[\'name-100\'];
        $dto->name300 = $data[\'name-300\'];
        $dto->name500 = null === $data[\'name-500\'] ? null : SomeCustomClass50000::fromArray($data[\'name-500\']);
        return $dto;
    }
}';

    /**
     * @return mixed[]
     */
    public function generateProvider(): array
    {
        return [
            [
                'payload' => [
                    'definition' => self::DEFINITION_EMPTY_PROPERTIES,
                    'codeGenerator' => [
                        'languageLevel' => '7.4',
                        'fullDocs' => false,
                    ],
                ],
                'expected' => self::EXAMPLE_FILE_CONTENT_EMPTY_PROPERTIES_SHORT_DOC_7_4,
            ],
            [
                'payload' => [
                    'definition' => self::DEFINITION_BASIC,
                    'codeGenerator' => [
                        'languageLevel' => '7.4',
                        'fullDocs' => false,
                    ],
                ],
                'expected' => self::EXAMPLE_FILE_CONTENT_BASIC_SHORT_DOC_7_4,
            ],
            [
                'payload' => [
                    'definition' => self::DEFINITION_EXTENDED,
                    'codeGenerator' => [
                        'languageLevel' => '7.4',
                        'fullDocs' => false,
                    ],
                ],
                'expected' => self::EXAMPLE_FILE_CONTENT_EXTENDED_SHORT_DOC_7_4,
            ],
            [
                'payload' => [
                    'definition' => self::DEFINITION_EMPTY_PROPERTIES,
                    'codeGenerator' => [
                        'languageLevel' => '7.4',
                        'fullDocs' => true,
                    ],
                ],
                'expected' => self::EXAMPLE_FILE_CONTENT_EMPTY_PROPERTIES_FULL_DOC_7_4,
            ],
            [
                'payload' => [
                    'definition' => self::DEFINITION_BASIC,
                    'codeGenerator' => [
                        'languageLevel' => '7.4',
                        'fullDocs' => true,
                    ],
                ],
                'expected' => self::EXAMPLE_FILE_CONTENT_BASIC_FULL_DOC_7_4,
            ],
            [
                'payload' => [
                    'definition' => self::DEFINITION_EXTENDED,
                    'codeGenerator' => [
                        'languageLevel' => '7.4',
                        'fullDocs' => true,
                    ],
                ],
                'expected' => self::EXAMPLE_FILE_CONTENT_EXTENDED_FULL_DOC_7_4,
            ],
        ];
    }

    /**
     * @param mixed[] $payload
     *
     * @dataProvider generateProvider
     */
    public function testGenerate(array $payload, string $expected): void
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
        Assert::assertSame($expected, $generatedFileDefinition->getFileContents());
    }

//    /**
//     * @deprecated Changed to compare by content.
//     *
//     * @param mixed[] $payload
//     *
//     * @dataProvider generateProvider
//     */
//    public function testGenerate(array $payload): void
//    {
//        $codeGenerator = new DtoCodeGenerator(
//            new BuilderFactory(),
//            new ScalarTypesResolver(),
//            $payload['codeGenerator']['languageLevel'],
//            $payload['codeGenerator']['fullDocs'],
//        );
//
//        $definition = $this->createDtoDefinition($payload['definition']);
//
//        $generatedFileDefinition = $codeGenerator->generate($definition);
//
//        Assert::assertSame($definition, $generatedFileDefinition->getClass());
//
//        $this->assertMatchesRegularExpression(
//            addslashes(sprintf(
//                '/namespace %s;/',
//                $payload['definition']['namespace']
//            )),
//            $generatedFileDefinition->getFileContents()
//        );
//
//        $this->assertMatchesRegularExpression(
//            addslashes(sprintf(
//                '/final class %s/',
//                $payload['definition']['class']
//            )),
//            $generatedFileDefinition->getFileContents()
//        );
//
//        $toArrayParams = [];
//
//        foreach ($payload['definition']['properties'] as $definitionProperty) {
//            if (array_key_exists('definition', $definitionProperty)) {
//                $this->assertMatchesRegularExpression(
//                    addslashes(sprintf(
//                        '/use %s\%s;/',
//                        $definitionProperty['definition']['namespace'],
//                        $definitionProperty['definition']['class']
//                    )),
//                    $generatedFileDefinition->getFileContents()
//                );
//            }
//
//            if (array_key_exists('description', $definitionProperty)) {
//                $this->assertMatchesRegularExpression(
//                    addslashes(sprintf(
//                        '/\* %s/',
//                        $definitionProperty['description']
//                    )),
//                    $generatedFileDefinition->getFileContents()
//                );
//            }
//
//            // REMARK: The PHP version should be checked here ($payload['codeGenerator']['languageLevel']).
//            // If it's 7.4 or higher, add a type hint to the expression template.
//            $paramTypeHint = ' [?]' . $definitionProperty['typeHint'];
//
//            $this->assertMatchesRegularExpression(
//                addslashes(sprintf(
//                    '/private%s [$]%s = null;/',
//                    $paramTypeHint,
//                    $definitionProperty['classPropertyName']
//                )),
//                $generatedFileDefinition->getFileContents()
//            );
//
//            if (array_key_exists('definition', $definitionProperty)) {
//                $toArrayParamValue = sprintf(
//                    'null === [$]this->%s [?] null : [$]this->%s->toArray[(][)]',
//                    $definitionProperty['classPropertyName'],
//                    $definitionProperty['classPropertyName']
//                );
//
//                if (! $definitionProperty['isInConstructor']) {
//                    $this->assertMatchesRegularExpression(
//                        addslashes(sprintf(
//                            '/[$]dto->%s = null === [$]data[[]\'%s\'[]] [?] null : %s::fromArray[(][$]data[[]\'%s\'[]][)];/',
//                            $definitionProperty['classPropertyName'],
//                            $definitionProperty['name'],
//                            $definitionProperty['definition']['class'],
//                            $definitionProperty['name']
//                        )),
//                        $generatedFileDefinition->getFileContents()
//                    );
//                }
//            } else {
//                $toArrayParamValue = sprintf('[$]this->%s', $definitionProperty['classPropertyName']);
//
//                if (! $definitionProperty['isInConstructor']) {
//                    $this->assertMatchesRegularExpression(
//                        addslashes(sprintf(
//                            '/[$]dto->%s = [$]data[[]\'%s\'[]];/',
//                            $definitionProperty['classPropertyName'],
//                            $definitionProperty['name']
//                        )),
//                        $generatedFileDefinition->getFileContents()
//                    );
//                }
//            }
//
//            if ($definitionProperty['isInConstructor']) {
//                $this->assertMatchesRegularExpression(
//                    addslashes(sprintf(
//                        '/[$]this->%s = [$]%s;/',
//                        $definitionProperty['classPropertyName'],
//                        $definitionProperty['classPropertyName']
//                    )),
//                    $generatedFileDefinition->getFileContents()
//                );
//            }
//
//            if ($definitionProperty['hasGetter']) {
//                $this->assertMatchesRegularExpression(
//                    addslashes(sprintf(
//                        '/public function get%s[(][)] : [?]%s/',
//                        ucfirst($definitionProperty['classPropertyName']),
//                        $definitionProperty['typeHint']
//                    )),
//                    $generatedFileDefinition->getFileContents()
//                );
//
//                Assert::assertNotFalse(
//                    preg_match(
//                        sprintf('/get%s[(]([^}])+[}]/', ucfirst($definitionProperty['classPropertyName'])),
//                        $generatedFileDefinition->getFileContents(),
//                        $matches
//                    )
//                );
//
//                $this->assertMatchesRegularExpression(
//                    addslashes(sprintf(
//                        '/return [$]this->%s;/',
//                        $definitionProperty['classPropertyName']
//                    )),
//                    $matches[0]
//                );
//
//                unset($matches);
//            }
//
//            if ($definitionProperty['hasSetter']) {
//                $this->assertMatchesRegularExpression(
//                    addslashes(sprintf(
//                        '/public function set%s[(][?]%s [$]%s[)]/',
//                        ucfirst($definitionProperty['classPropertyName']),
//                        $definitionProperty['typeHint'],
//                        $definitionProperty['classPropertyName']
//                    )),
//                    $generatedFileDefinition->getFileContents()
//                );
//
//                Assert::assertNotFalse(
//                    preg_match(
//                        sprintf('/set%s[(]([^}])+[}]/', ucfirst($definitionProperty['classPropertyName'])),
//                        $generatedFileDefinition->getFileContents(),
//                        $matches
//                    )
//                );
//
//                $this->assertMatchesRegularExpression(
//                    addslashes(sprintf(
//                        '/[$]this->%s = [$]%s;/',
//                        $definitionProperty['classPropertyName'],
//                        $definitionProperty['classPropertyName']
//                    )),
//                    $matches[0]
//                );
//
//                $this->assertMatchesRegularExpression(
//                    addslashes('/return [$]this;/'),
//                    $matches[0]
//                );
//
//                unset($matches);
//            }
//
//            $toArrayParams[] = sprintf("'%s' => %s", $definitionProperty['name'], $toArrayParamValue);
//        }
//
//        $this->assertMatchesRegularExpression(
//            addslashes(sprintf(
//                '/return array[(]%s[)];/',
//                $toArrayParams ? implode(', ', $toArrayParams) : null
//            )),
//            $generatedFileDefinition->getFileContents()
//        );
//
////        dump($generatedFileDefinition->getFileContents());
////        exit;
//    }

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
            ))
                ->setClassPropertyName($property['classPropertyName'])
                ->setInConstructor($property['isInConstructor']);

            if ($property['hasGetter']) {
                $propertyDefinition->setHasGetter(true);
                $propertyDefinition->setGetterName('get' . ucfirst($property['classPropertyName']));
            }

            if ($property['hasSetter']) {
                $propertyDefinition->setHasSetter(true);
                $propertyDefinition->setSetterName('set' . ucfirst($property['classPropertyName']));
            }

            if (array_key_exists('definition', $property)) {
                $subDefinition = $this->createDtoDefinition($property['definition']);
                $propertyDefinition->setObjectTypeDefinition($subDefinition);
            }

            $definitionProperties[] = $propertyDefinition;
        }

        $definition = new DtoDefinition($definitionProperties);
        $definition
            ->setNamespace($payload['namespace'])
            ->setClassName($payload['class']);

        return $definition;
    }
}
