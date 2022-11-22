<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Unit\CodeGenerator\PhpParserGenerators;

use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\ClassDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\GeneratedFileDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\GeneratedInterfaceDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\RequestHandlerInterfaceDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\PhpParserGenerators\InterfaceCodeGenerator;
use OnMoon\OpenApiServerBundle\Interfaces\ResponseDto;
use OnMoon\OpenApiServerBundle\Types\ScalarTypesResolver;
use PhpParser\BuilderFactory;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OnMoon\OpenApiServerBundle\CodeGenerator\PhpParserGenerators\InterfaceCodeGenerator
 */
class InterfaceCodeGeneratorTest extends TestCase
{
    private InterfaceCodeGenerator $interfaceCodeGenerator;

    protected function setUp(): void
    {
        $builderFactory               = new BuilderFactory();
        $scalarTypeResolver           = new ScalarTypesResolver();
        $languageLevel                = '7.4';
        $fullDocs                     = true;
        $this->interfaceCodeGenerator = new InterfaceCodeGenerator(
            $builderFactory,
            $scalarTypeResolver,
            $languageLevel,
            $fullDocs,
        );
    }

    protected function tearDown(): void
    {
        unset($this->interfaceCodeGenerator);
        parent::tearDown();
    }

    public function testGenerateWithNotExtendedGeneratedInterfaceDefinition(): void
    {
        $generatedInterfaceDefinition = new GeneratedInterfaceDefinition();
        $generatedInterfaceDefinition->setNamespace('Test\Test2');
        $generatedInterfaceDefinition->setClassName('TestClass');

        $generatedCode                   =
            <<<EOD
<?php

declare (strict_types=1);
namespace Test\Test2;

/**
 * This interface was automatically generated
 * You should not change it manually as it will be overwritten
 */
interface TestClass
{
}
EOD;
        $expectedGeneratedFileDefinition = new GeneratedFileDefinition(
            $generatedInterfaceDefinition,
            $generatedCode
        );
        $generatedFileDefinition         = $this->interfaceCodeGenerator->generate($generatedInterfaceDefinition);

        Assert::assertEquals($expectedGeneratedFileDefinition, $generatedFileDefinition);
    }

    public function testGenerateWithExtendedGeneratedInterfaceDefinition(): void
    {
        $generatedInterfaceDefinition = new GeneratedInterfaceDefinition();
        $generatedInterfaceDefinition->setExtends(ClassDefinition::fromFQCN(ResponseDto::class));
        $generatedInterfaceDefinition->setNamespace('Test\Test2');
        $generatedInterfaceDefinition->setClassName('TestClass');

        $generatedCode                   =
            <<<EOD
<?php

declare (strict_types=1);
namespace Test\Test2;

use OnMoon\OpenApiServerBundle\Interfaces\ResponseDto;
/**
 * This interface was automatically generated
 * You should not change it manually as it will be overwritten
 */
interface TestClass extends ResponseDto
{
}
EOD;
        $expectedGeneratedFileDefinition = new GeneratedFileDefinition(
            $generatedInterfaceDefinition,
            $generatedCode
        );
        $generatedFileDefinition         = $this->interfaceCodeGenerator->generate($generatedInterfaceDefinition);

        Assert::assertEquals($expectedGeneratedFileDefinition, $generatedFileDefinition);
    }

    public function testGenerateWithRequestHandlerInterfaceDefinition(): void
    {
        $generatedInterfaceDefinition = new RequestHandlerInterfaceDefinition();

        $generatedInterfaceDefinition->setClassName('TestClass');
        $generatedInterfaceDefinition->setMethodName('test');
        $generatedInterfaceDefinition->setNamespace('Test\Test2');

        $generatedCode                   =
            <<<EOD
<?php

declare (strict_types=1);
namespace Test\Test2;

/**
 * This interface was automatically generated
 * You should not change it manually as it will be overwritten
 */
interface TestClass
{
    public function test() : void;
}
EOD;
        $expectedGeneratedFileDefinition = new GeneratedFileDefinition(
            $generatedInterfaceDefinition,
            $generatedCode
        );
        $generatedFileDefinition         = $this->interfaceCodeGenerator->generate($generatedInterfaceDefinition);

        Assert::assertEquals($expectedGeneratedFileDefinition, $generatedFileDefinition);
    }

    public function testGenerateWithRequestHandlerInterfaceDefinitionAndRequestedType(): void
    {
        $generatedInterfaceDefinition = new RequestHandlerInterfaceDefinition();

        $generatedInterfaceDefinition->setClassName('TestClass');
            $generatedInterfaceDefinition->setMethodName('test');
            $generatedInterfaceDefinition->setNamespace('Test\Test2');
            $generatedInterfaceDefinition->setRequestType(ClassDefinition::fromFQCN('TestClass'));

        $generatedCode                   =
            <<<EOD
<?php

declare (strict_types=1);
namespace Test\Test2;

use \TestClass as TestClass_;
/**
 * This interface was automatically generated
 * You should not change it manually as it will be overwritten
 */
interface TestClass
{
    /** @param TestClass_ \$request */
    public function test(TestClass_ \$request) : void;
}
EOD;
        $expectedGeneratedFileDefinition = new GeneratedFileDefinition(
            $generatedInterfaceDefinition,
            $generatedCode
        );
        $generatedFileDefinition         = $this->interfaceCodeGenerator->generate($generatedInterfaceDefinition);

        Assert::assertEquals($expectedGeneratedFileDefinition, $generatedFileDefinition);
    }

    public function testGenerateWithRequestHandlerInterfaceDefinitionAndResponseType(): void
    {
        $generatedInterfaceDefinition = new RequestHandlerInterfaceDefinition();

        $generatedInterfaceDefinition->setClassName('TestClass');
        $generatedInterfaceDefinition->setMethodName('test');
        $generatedInterfaceDefinition->setNamespace('Test\Test2');
        $generatedInterfaceDefinition->setResponseTypes(ClassDefinition::fromFQCN('TestClass'));

        $generatedCode                   =
            <<<EOD
<?php

declare (strict_types=1);
namespace Test\Test2;

use \TestClass as TestClass_;
/**
 * This interface was automatically generated
 * You should not change it manually as it will be overwritten
 */
interface TestClass
{
    /** @return TestClass_ */
    public function test() : TestClass_;
}
EOD;
        $expectedGeneratedFileDefinition = new GeneratedFileDefinition(
            $generatedInterfaceDefinition,
            $generatedCode
        );
        $generatedFileDefinition         = $this->interfaceCodeGenerator->generate($generatedInterfaceDefinition);

        Assert::assertEquals($expectedGeneratedFileDefinition, $generatedFileDefinition);
    }

    public function testGenerateWithRequestHandlerInterfaceDefinitionAndDescription(): void
    {
        $generatedInterfaceDefinition = new RequestHandlerInterfaceDefinition();

        $generatedInterfaceDefinition->setClassName('TestClass');
        $generatedInterfaceDefinition->setMethodName('test');
        $generatedInterfaceDefinition->setNamespace('Test\Test2');
        $generatedInterfaceDefinition->setMethodDescription('method description');

        $generatedCode                   =
            <<<EOD
<?php

declare (strict_types=1);
namespace Test\Test2;

/**
 * This interface was automatically generated
 * You should not change it manually as it will be overwritten
 */
interface TestClass
{
    /** method description */
    public function test() : void;
}
EOD;
        $expectedGeneratedFileDefinition = new GeneratedFileDefinition(
            $generatedInterfaceDefinition,
            $generatedCode
        );
        $generatedFileDefinition         = $this->interfaceCodeGenerator->generate($generatedInterfaceDefinition);

        Assert::assertEquals($expectedGeneratedFileDefinition, $generatedFileDefinition);
    }
}
