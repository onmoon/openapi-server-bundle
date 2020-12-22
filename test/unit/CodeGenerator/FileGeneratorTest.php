<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator;

use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\ClassDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\DtoDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\GraphDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\OperationDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\PropertyDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\RequestDtoDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\RequestHandlerInterfaceDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\ResponseDtoDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\ServiceSubscriberDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\SpecificationDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\PhpParserGenerators\DtoCodeGenerator;
use OnMoon\OpenApiServerBundle\CodeGenerator\PhpParserGenerators\InterfaceCodeGenerator;
use OnMoon\OpenApiServerBundle\CodeGenerator\PhpParserGenerators\ServiceSubscriberCodeGenerator;
use OnMoon\OpenApiServerBundle\Specification\Definitions\Property;
use OnMoon\OpenApiServerBundle\Specification\Definitions\SpecificationConfig;
use OnMoon\OpenApiServerBundle\Types\ScalarTypesResolver;
use PhpParser\BuilderFactory;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OnMoon\OpenApiServerBundle\CodeGenerator\FileGenerator
 */
class FileGeneratorTest extends TestCase
{
    private FileGenerator $fileGenerator;

    public function setUp(): void
    {
        $dtoCodeGenerator = new DtoCodeGenerator(
            new BuilderFactory(),
            new ScalarTypesResolver(),
            '7.4',
            true
        );

        $serviceSubscriberCodeGenerator = new ServiceSubscriberCodeGenerator(
            new BuilderFactory(),
            new ScalarTypesResolver(),
            '7.4',
            true
        );

        $interfaceCodeGenerator = new InterfaceCodeGenerator(
            new BuilderFactory(),
            new ScalarTypesResolver(),
            '7.4',
            true
        );

        $this->fileGenerator = new FileGenerator(
            $dtoCodeGenerator,
            $interfaceCodeGenerator,
            $serviceSubscriberCodeGenerator
        );
    }

    public function testGenerateAllFiles(): void
    {
        $requestDtoDefinition = new RequestDtoDefinition(null, null, null);
        $requestDtoDefinition->setNamespace('NamespaceOne\NamespaceTwo');
        $requestDtoDefinition->setClassName('ClassNameOne');
        $requestDtoDefinition->setFilePath('SomeFilePath');
        $requestDtoDefinition->setFileName('SomeRandomFileName');

        $propertyOne = new Property('locator');

        $propertyDefinitionOne = new PropertyDefinition($propertyOne);
        $propertyDefinitionOne->setClassPropertyName('TestClassPropertyNameOne');

        $propertyTwo = new Property('someProperty');

        $propertyDefinitionTwo = new PropertyDefinition($propertyTwo);
        $propertyDefinitionTwo->setClassPropertyName('TestClassPropertyNameTwo');

        $randomDtoDefinition = new DtoDefinition([]);
        $randomDtoDefinition->setNamespace('NamespaceSix');
        $randomDtoDefinition->setClassName('TestClassNameSix');
        $propertyDefinitionTwo->setObjectTypeDefinition($randomDtoDefinition);

        $dtoDefinition = new DtoDefinition([$propertyDefinitionTwo]);
        $dtoDefinition->setNamespace('NamespaceOne\NamespaceTwo');
        $dtoDefinition->setClassName('TestClassNameTwo');

        $propertyDefinitionOne->setObjectTypeDefinition($dtoDefinition);

        $responseDtoDefinition = new ResponseDtoDefinition(
            '200',
            [$propertyDefinitionOne]
        );
        $responseDtoDefinition->setNamespace('NamespaceFour\NamespaceFive');
        $responseDtoDefinition->setClassName('TestClassNameThree');

        $operationDefinition = new OperationDefinition(
            '/',
            'get',
            'test',
            'test',
            null,
            $requestDtoDefinition,
            [$responseDtoDefinition]
        );

        $operationDefinitionTwo = new OperationDefinition(
            '/',
            'get',
            'test',
            'test',
            null,
            $requestDtoDefinition,
            [$responseDtoDefinition]
        );

        $requestHandlerInterfaceDefinition = new RequestHandlerInterfaceDefinition();
        $requestHandlerInterfaceDefinition->setNamespace('NamespaceThree\NamespaceFour');
        $requestHandlerInterfaceDefinition->setClassName('SomeClass');
        $requestHandlerInterfaceDefinition->setMethodName('test');

        $operationDefinition->setRequestHandlerInterface($requestHandlerInterfaceDefinition);
        $operationDefinition->setMarkersInterface(ClassDefinition::fromFQCN('NamespaceThree\NamespaceFour\SomeClass'));

        $operationDefinitionTwo->setRequestHandlerInterface($requestHandlerInterfaceDefinition);
        $operationDefinitionTwo->setMarkersInterface(ClassDefinition::fromFQCN('NamespaceThree\NamespaceFour\SomeClass'));

        $specificationDefinition = new SpecificationDefinition(
            new SpecificationConfig('/', null, '/', 'application/json'),
            [$operationDefinition, $operationDefinitionTwo]
        );

        $serviceSubscriberDefinition = new ServiceSubscriberDefinition();
        $serviceSubscriberDefinition->setNamespace('NamespaceOne\NamespaceTwo');
        $serviceSubscriberDefinition->setClassName('ClassName');

        $classDefinitionOne = ClassDefinition::fromFQCN('NamespaceOne\NamespaceTwo\ClassName');
        $classDefinitionTwo = ClassDefinition::fromFQCN('NamespaceOne\NamespaceTwo\ClassName');
        $serviceSubscriberDefinition->setImplements([$classDefinitionOne, $classDefinitionTwo]);

        $graphDefinition = new GraphDefinition(
            [$specificationDefinition],
            $serviceSubscriberDefinition
        );

        $result = $this->fileGenerator->generateAllFiles($graphDefinition);

        $expectedFileZeroContent = <<<'EOD'
<?php

declare (strict_types=1);
namespace NamespaceOne\NamespaceTwo;

/**
 * This class was automatically generated
 * You should not change it manually as it will be overwritten
 */
final class ClassNameOne
{
    /** @inheritDoc */
    public function toArray() : array
    {
        return array();
    }
    /** @inheritDoc */
    public static function fromArray(array $data) : self
    {
        return new ClassNameOne();
    }
}
EOD;

        self::assertEquals($result[0]->getFileContents(), $expectedFileZeroContent);
        self::assertEquals($result[0]->getClass()->getFQCN(), 'NamespaceOne\NamespaceTwo\ClassNameOne');
        self::assertEquals($result[0]->getClass()->getFilePath(), 'SomeFilePath');
        self::assertEquals($result[0]->getClass()->getFileName(), 'SomeRandomFileName');

        $expectedFileOneContent = <<<'EOD'
<?php

declare (strict_types=1);
namespace NamespaceFour\NamespaceFive;

use NamespaceOne\NamespaceTwo\TestClassNameTwo;
/**
 * This class was automatically generated
 * You should not change it manually as it will be overwritten
 */
final class TestClassNameThree
{
    /** @var TestClassNameTwo|null $TestClassPropertyNameOne */
    private ?TestClassNameTwo $TestClassPropertyNameOne = null;
    /** @return string */
    public static function _getResponseCode() : string
    {
        return '200';
    }
    /** @inheritDoc */
    public function toArray() : array
    {
        return array('locator' => null === $this->TestClassPropertyNameOne ? null : $this->TestClassPropertyNameOne->toArray());
    }
    /** @inheritDoc */
    public static function fromArray(array $data) : self
    {
        $dto = new TestClassNameThree();
        $dto->TestClassPropertyNameOne = null === $data['locator'] ? null : TestClassNameTwo::fromArray($data['locator']);
        return $dto;
    }
}
EOD;
        self::assertEquals($result[1]->getFileContents(), $expectedFileOneContent);
        self::assertEquals($result[1]->getClass()->getFQCN(), 'NamespaceFour\NamespaceFive\TestClassNameThree');

        $expectedFileTenContent = <<<'EOD'
<?php

declare (strict_types=1);
namespace NamespaceOne\NamespaceTwo;

use Psr\Container\ContainerInterface;
use OnMoon\OpenApiServerBundle\Interfaces\RequestHandler;
use NamespaceThree\NamespaceFour\SomeClass;
/**
 * This class was automatically generated
 * You should not change it manually as it will be overwritten
 */
class ClassName implements ClassName, ClassName
{
    /** @var ContainerInterface */
    private ContainerInterface $locator;
    /** @param ContainerInterface $locator */
    public function __construct(ContainerInterface $locator)
    {
        $this->locator = $locator;
    }
    /**
     * @inheritDoc
     */
    public static function getSubscribedServices()
    {
        return array('test' => '?' . SomeClass::class, 'test' => '?' . SomeClass::class);
    }
EOD;
        self::assertStringContainsString($expectedFileTenContent, $result[10]->getFileContents());
    }

    public function testGenerateDtoTree(): void
    {
        $propertyOne           = new Property('testPropertyOne');
        $propertyDefinitionOne = new PropertyDefinition($propertyOne);
        $propertyDefinitionOne->setClassPropertyName('TestClassPropertyNameOne');

        $dtoDefinitionOne = new DtoDefinition([$propertyDefinitionOne]);
        $dtoDefinitionOne->setNamespace('NamespaceOne\NamespaceTwo');
        $dtoDefinitionOne->setClassName('ClassNameOne');
        $dtoDefinitionOne->setFilePath('SomeFilePath');
        $dtoDefinitionOne->setFileName('SomeRandomFileName');

        $dtoDefinitionTwo = new DtoDefinition([]);
        $dtoDefinitionTwo->setNamespace('NamespaceOne\NamespaceTwo');
        $dtoDefinitionTwo->setClassName('ClassNameTwo');

        $propertyDefinitionOne->setObjectTypeDefinition($dtoDefinitionTwo);

        $result                  = $this->fileGenerator->generateDtoTree($dtoDefinitionOne);
        $expectedFileZeroContent = <<<'EOD'
<?php

declare (strict_types=1);
namespace NamespaceOne\NamespaceTwo;

/**
 * This class was automatically generated
 * You should not change it manually as it will be overwritten
 */
final class ClassNameOne
{
    /** @var ClassNameTwo|null $TestClassPropertyNameOne */
    private ?ClassNameTwo $TestClassPropertyNameOne = null;
    /** @inheritDoc */
    public function toArray() : array
    {
        return array('testPropertyOne' => null === $this->TestClassPropertyNameOne ? null : $this->TestClassPropertyNameOne->toArray());
    }
    /** @inheritDoc */
    public static function fromArray(array $data) : self
    {
        $dto = new ClassNameOne();
        $dto->TestClassPropertyNameOne = null === $data['testPropertyOne'] ? null : ClassNameTwo::fromArray($data['testPropertyOne']);
        return $dto;
    }
}
EOD;

        self::assertEquals($result[0]->getFileContents(), $expectedFileZeroContent);
        self::assertEquals($result[0]->getClass()->getFilePath(), 'SomeFilePath');
        self::assertEquals($result[0]->getClass()->getFileName(), 'SomeRandomFileName');
        self::assertEquals($result[0]->getClass()->getFQCN(), 'NamespaceOne\NamespaceTwo\ClassNameOne');
    }
}
