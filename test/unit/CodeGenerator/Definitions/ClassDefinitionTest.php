<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Unit\CodeGenerator\Definitions;

use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\ClassDefinition;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

/** @covers \OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\ClassDefinition */
final class ClassDefinitionTest extends TestCase
{
    public function testCorrectDefinitionCreatedFromFQCN(): void
    {
        $fqcn = 'NamespaceOne\NamespaceTwo\ClassName';

        $classDefinition = ClassDefinition::fromFQCN($fqcn);

        Assert::assertSame($fqcn, $classDefinition->getFQCN());
        Assert::assertSame('ClassName', $classDefinition->getClassName());
        Assert::assertSame('NamespaceOne\NamespaceTwo', $classDefinition->getNamespace());
    }

    public function testCorrectDefinitionCreatedFromFQCNInRootNamespace(): void
    {
        $fqcn = 'ClassName';

        $classDefinition = ClassDefinition::fromFQCN($fqcn);

        Assert::assertSame('\\ClassName', $classDefinition->getFQCN());
        Assert::assertSame('ClassName', $classDefinition->getClassName());
        Assert::assertSame('', $classDefinition->getNamespace());
    }

    public function testSetClassNameChangesClassName(): void
    {
        $fqcn = 'NamespaceOne\NamespaceTwo\ClassName';

        $classDefinition = ClassDefinition::fromFQCN($fqcn);

        Assert::assertSame($fqcn, $classDefinition->getFQCN());
        Assert::assertSame('ClassName', $classDefinition->getClassName());

        $otherClassName = 'OtherClassName';
        $classDefinition->setClassName($otherClassName);

        Assert::assertNotSame('ClassName', $classDefinition->getClassName());
        Assert::assertSame($otherClassName, $classDefinition->getClassName());
    }

    public function testSetNamespaceChangesNamespace(): void
    {
        $fqcn = 'NamespaceOne\NamespaceTwo\ClassName';

        $classDefinition = ClassDefinition::fromFQCN($fqcn);

        Assert::assertSame($fqcn, $classDefinition->getFQCN());
        Assert::assertSame('ClassName', $classDefinition->getClassName());

        $otherNamespace = 'NamespaceOne\NamespaceThree';
        $classDefinition->setNamespace($otherNamespace);

        Assert::assertNotSame('NamespaceOne\NamespaceTwo', $classDefinition->getNamespace());
        Assert::assertSame($otherNamespace, $classDefinition->getNamespace());
    }
}
