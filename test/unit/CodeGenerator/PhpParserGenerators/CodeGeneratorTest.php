<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Unit\CodeGenerator\PhpParserGenerators;

use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\ClassDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\DtoDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\PropertyDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\PhpParserGenerators\CodeGenerator;
use OnMoon\OpenApiServerBundle\CodeGenerator\PhpParserGenerators\FileBuilder;
use OnMoon\OpenApiServerBundle\Specification\Definitions\Property;
use OnMoon\OpenApiServerBundle\Types\ScalarTypesResolver;
use PhpParser\BuilderFactory;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Throwable;

use const PHP_EOL;

/**
 * @covers \OnMoon\OpenApiServerBundle\CodeGenerator\PhpParserGenerators\CodeGenerator;
 */
class CodeGeneratorTest extends TestCase
{
    private CodeGenerator $codeGenerator;

    public function setUp(): void
    {
        $this->codeGenerator = new class (new BuilderFactory(), new ScalarTypesResolver(), '7.4', true) extends CodeGenerator{
        };
    }

    public function tearDown(): void
    {
        unset($this->codeGenerator);
        parent::tearDown();
    }

    public function testGetTypeDocBlockAndGetTypePhpScalarTypeArrayableAndNullable(): void
    {
        $property = new Property('test');
        $property->setArray(true);
        $property->setScalarTypeId(0);
        $propertyDefinition = new PropertyDefinition($property);
        $fileBuilder        = new FileBuilder(ClassDefinition::fromFQCN('\TestNamespace\TestClass'));

        $typeDoc = $this->codeGenerator->getTypeDocBlock($fileBuilder, $propertyDefinition);
        Assert::assertEquals('string[]|null', $typeDoc);

        $typePhp = $this->codeGenerator->getTypePhp($fileBuilder, $propertyDefinition);
        Assert::assertEquals('?array', $typePhp);
    }

    public function testGetTypeDocBlockAndGetTypePhpObjectNotArrayableNotNullable(): void
    {
        $property = new Property('test');
        $property->setArray(false);
        $propertyDefinition = new PropertyDefinition($property);
        $propertyDefinition->setNullable(false);
        $dtoDefinition = new DtoDefinition([]);
        $propertyDefinition->setObjectTypeDefinition($dtoDefinition);
        $fileBuilderMock   = $this->createMock(FileBuilder::class);
        $expectedClassName = 'TestClassName';
        $fileBuilderMock->method('getReference')->with($dtoDefinition)->willReturn($expectedClassName);

        $typeDoc = $this->codeGenerator->getTypeDocBlock($fileBuilderMock, $propertyDefinition);
        Assert::assertEquals($expectedClassName, $typeDoc);

        $typePhp = $this->codeGenerator->getTypePhp($fileBuilderMock, $propertyDefinition);
        Assert::assertEquals($expectedClassName, $typePhp);
    }

    public function testGetTypeNameReturnsClassViaObjectType(): void
    {
        $propertyDefinition = new PropertyDefinition(new Property('test'));
        $dtoDefinition      = new DtoDefinition([]);
        $propertyDefinition->setObjectTypeDefinition($dtoDefinition);
        $fileBuilderMock   = $this->createMock(FileBuilder::class);
        $expectedClassName = 'TestClassName';
        $fileBuilderMock->expects(self::once())->method('getReference')->with($dtoDefinition)->willReturn($expectedClassName);

        $typeName = $this->codeGenerator->getTypeName($fileBuilderMock, $propertyDefinition);

        Assert::assertEquals($expectedClassName, $typeName);
    }

    public function testGetTypeNameThrowsException(): void
    {
        $propertyDefinition = new PropertyDefinition(new Property('test'));
        $fileBuilder        = new FileBuilder(ClassDefinition::fromFQCN('\TestNamespace\TestClass'));
        $this->expectException(Throwable::class);
        $this->expectExceptionMessage('One of ObjectTypeDefinition and ScalarTypeId should not be null');
        $this->codeGenerator->getTypeName($fileBuilder, $propertyDefinition);
    }

    public function testGetTypeNameReturnsClassViaScalarType(): void
    {
        $property = new Property('test');
        $property->setScalarTypeId(0);
        $propertyDefinition = new PropertyDefinition($property);
        $fileBuilder        = new FileBuilder(ClassDefinition::fromFQCN('\TestNamespace\TestClass'));

        $typeName = $this->codeGenerator->getTypeName($fileBuilder, $propertyDefinition);

        Assert::assertEquals('string', $typeName);
    }

    public function testGetDocCommentOneLine(): void
    {
        $docComment         = $this->codeGenerator->getDocComment([' @inheritDoc']);
        $expectedDocComment = '/** @inheritDoc */';
        Assert::assertEquals($expectedDocComment, $docComment);
    }

    public function testGetDocCommentManyLines(): void
    {
        $docComment         = $this->codeGenerator->getDocComment(['@param mixed[]', ' @return string']);
        $expectedDocComment = '/**' . PHP_EOL .
            ' * @param mixed[]' . PHP_EOL .
            ' * @return string' . PHP_EOL .
            ' */';
        Assert::assertEquals($expectedDocComment, $docComment);
    }

    public function testPrintFile(): void
    {
        $expectedFileContent = <<<'EOD'
<?php

declare (strict_types=1);
namespace \TestNamespace;

EOD;
        $fileBuilder         = new FileBuilder(ClassDefinition::fromFQCN('\TestNamespace\TestClass'));
        $fileContent         = $this->codeGenerator->printFile($fileBuilder);
        Assert::assertEquals($expectedFileContent, $fileContent);
    }
}
