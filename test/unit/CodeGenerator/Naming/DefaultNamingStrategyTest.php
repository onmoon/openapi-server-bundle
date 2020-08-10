<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Unit\CodeGenerator\Naming;

use OnMoon\OpenApiServerBundle\CodeGenerator\Naming\CannotCreateNamespace;
use OnMoon\OpenApiServerBundle\CodeGenerator\Naming\CannotCreatePropertyName;
use OnMoon\OpenApiServerBundle\CodeGenerator\Naming\DefaultNamingStrategy;
use PHPUnit\Framework\TestCase;
use sspat\ReservedWords\ReservedWords;

use function Safe\sprintf;

/**
 * @covers \OnMoon\OpenApiServerBundle\CodeGenerator\Naming\DefaultNamingStrategy
 */
class DefaultNamingStrategyTest extends TestCase
{
    public function setUp(): void
    {
        $reservedWords = new ReservedWords(['SomeReservedWord']);
        $this->defaultNamingStrategy = new DefaultNamingStrategy($reservedWords, 'NameSpace', '0');
    }

    public function isAllowedPhpPropertyNameDataProvider(): array
    {
        return [
            ['test', true],
            ['SomeCamelCase', true],
            ['Spaces are not allowed', false],
            ['&^%$!@#', false],
        ];
    }

    /**
     * @dataProvider isAllowedPhpPropertyNameDataProvider
     */
    public function testIsAllowedPhpPropertyName($name, $expectedResult): void
    {
        $actualResult = $this->defaultNamingStrategy->isAllowedPhpPropertyName($name);

        $this->assertEquals($expectedResult, $actualResult);
    }


    public function stringToMethodNameDataProvider(): array
    {
        return [
            ['test', 'test'],
            ['some Random Phrase', 'someRandomPhrase'],
            ['SomeReservedWord', 'someReservedWord'],
            ['1test', '_1test'],
            ['9999', '_9999'],
        ];
    }

    /**
     * @dataProvider stringToMethodNameDataProvider
     */
    public function testStringToMethodNameData($string, $expectedOutput): void
    {
        $actualOutput  = $this->defaultNamingStrategy->stringToMethodName($string);

        TestCase::assertEquals($actualOutput, $expectedOutput);
    }

    public function testGetInterfaceFQCN(): void
    {
        $expectedOutput = 'NameSpace\Apis\test\_123\_123';
        $actualOutput   = $this->defaultNamingStrategy->getInterfaceFQCN('test', '123');

        TestCase::assertEquals($expectedOutput, $actualOutput);
    }

    public function testStringToNamespace(): void
    {
        $expectedOutput = 'SomeRandomString';
        $actualOutput = $this->defaultNamingStrategy->stringToNamespace('SomeRandomString');

        TestCase::assertEquals($expectedOutput, $actualOutput);
    }

    public function testStringToNamespaceThrowsExceptionIfEmptyString(): void
    {
        $this->expectException(CannotCreateNamespace::class);
        $this->expectExceptionMessage("Cannot create namespace from text: . Text contains no characters that can be used.");

        $this->defaultNamingStrategy->stringToNamespace('');
    }

    public function testStringToMethodName(): void
    {
        $expectedOutput = 'someRandomString';
        $actualOutput = $this->defaultNamingStrategy->stringToMethodName('SomeRandomString');

        TestCase::assertEquals($expectedOutput, $actualOutput);
    }

    public function testStringToMethodNameThrowsExceptionIfEmptyString(): void
    {
        $this->expectException(CannotCreatePropertyName::class);
        $this->expectExceptionMessage("Cannot create property name from text: . Text contains no characters that can be used.");

        $this->defaultNamingStrategy->stringToMethodName('');
    }

    public function testBuildNamespaceReturnsCorrectNamespace(): void
    {
        $expectedOutput = 'hello\world';
        $actualOutput   = $this->defaultNamingStrategy->buildNamespace('hello', 'world');

        TestCase::assertEquals($expectedOutput, $actualOutput);
    }

    public function testBuildPathReturnsCorrectPath(): void
    {
        $expectedOutput = 'hello' . DIRECTORY_SEPARATOR . 'world';
        $actualOutput   = $this->defaultNamingStrategy->buildPath('hello', 'world');

        TestCase::assertEquals($expectedOutput, $actualOutput);
    }
}
