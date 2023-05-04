<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Unit\CodeGenerator\Naming;

use OnMoon\OpenApiServerBundle\CodeGenerator\Naming\CannotCreateNamespace;
use OnMoon\OpenApiServerBundle\CodeGenerator\Naming\CannotCreatePropertyName;
use OnMoon\OpenApiServerBundle\CodeGenerator\Naming\DefaultNamingStrategy;
use PHPUnit\Framework\TestCase;
use sspat\ReservedWords\ReservedWords;

use const DIRECTORY_SEPARATOR;

/**
 * @covers \OnMoon\OpenApiServerBundle\CodeGenerator\Naming\DefaultNamingStrategy
 */
final class DefaultNamingStrategyTest extends TestCase
{
    private DefaultNamingStrategy $defaultNamingStrategy;

    public function setUp(): void
    {
        $someReservedWords           = ['namespace' => '8.0', 'method' => '7.4'];
        $reservedWords               = new ReservedWords(['somereservedword' => $someReservedWords]);
        $this->defaultNamingStrategy = new DefaultNamingStrategy($reservedWords, 'NameSpace', '8.0');
    }

    /**
     * @return array<int, array<int, bool|string>>
     */
    public function isAllowedPhpPropertyNameDataProvider(): array
    {
        return [
            ['test', true],
            ['SomeCamelCase', true],
            ['Spaces are not allowed', false],
            ['&^%$!@#', false],
            ['1SomeClass', false],
            ['SomeClass1', true],
            ['SOMEUPPERCASECLASS', true],
        ];
    }

    /**
     * @dataProvider isAllowedPhpPropertyNameDataProvider
     */
    public function testIsAllowedPhpPropertyName(string $name, bool $expectedResult): void
    {
        $actualResult = $this->defaultNamingStrategy->isAllowedPhpPropertyName($name);

        TestCase::assertEquals($expectedResult, $actualResult);
    }

    /**
     * @return array<int, array<int, bool|string>>
     */
    public function stringToMethodNameDataProvider(): array
    {
        return [
            ['test', 'test'],
            ['some Random Phrase', 'someRandomPhrase'],
            ['SomeReservedWord', '_someReservedWord'],
            ['1test', '_1test'],
            ['9999', '_9999'],
        ];
    }

    /**
     * @dataProvider stringToMethodNameDataProvider
     */
    public function testStringToMethodNameData(string $string, string $expectedOutput): void
    {
        $actualOutput = $this->defaultNamingStrategy->stringToMethodName($string);

        TestCase::assertEquals($expectedOutput, $actualOutput);
    }

    public function testGetInterfaceFQCN(): void
    {
        $expectedOutput = 'NameSpace\Apis\test\_123\_123';
        $actualOutput   = $this->defaultNamingStrategy->getInterfaceFQCN('test', '123');

        TestCase::assertEquals($expectedOutput, $actualOutput);
    }

    /**
     * @return array<int, array<int, bool|string>>
     */
    public function stringToNamespaceDataProvider(): array
    {
        return [
            ['1some random string', '_1someRandomString'],
            ['SomeReservedWord', '_SomeReservedWord'],
            ['Some random 1 string', 'SomeRandom1String'],
        ];
    }

    /**
     * @dataProvider stringToNamespaceDataProvider
     */
    public function testStringToNamespace(string $string, string $expectedOutput): void
    {
        $actualOutput = $this->defaultNamingStrategy->stringToNamespace($string);

        TestCase::assertEquals($expectedOutput, $actualOutput);
    }

    public function testStringToNamespaceThrowsExceptionIfEmptyString(): void
    {
        $this->expectException(CannotCreateNamespace::class);
        $this->expectExceptionMessage('Cannot create namespace from text: . Text contains no characters that can be used.');

        $this->defaultNamingStrategy->stringToNamespace('');
    }

    public function testStringToMethodName(): void
    {
        $expectedOutput = 'someRandomString';
        $actualOutput   = $this->defaultNamingStrategy->stringToMethodName('SomeRandomString');

        TestCase::assertEquals($expectedOutput, $actualOutput);
    }

    public function testStringToMethodNameThrowsExceptionIfEmptyString(): void
    {
        $this->expectException(CannotCreatePropertyName::class);
        $this->expectExceptionMessage('Cannot create property name from text: . Text contains no characters that can be used.');

        $this->defaultNamingStrategy->stringToMethodName('');
    }

    public function testBuildNamespaceReturnsCorrectNamespace(): void
    {
        $expectedOutput = 'hello\world';
        $actualOutput   = $this->defaultNamingStrategy->buildNamespace('\hello', 'world');

        TestCase::assertEquals($expectedOutput, $actualOutput);
    }

    public function testBuildPathReturnsCorrectPath(): void
    {
        $expectedOutput = 'hello' . DIRECTORY_SEPARATOR . 'world';
        $actualOutput   = $this->defaultNamingStrategy->buildPath('hello', 'world' . DIRECTORY_SEPARATOR);

        TestCase::assertEquals($expectedOutput, $actualOutput);
    }
}
