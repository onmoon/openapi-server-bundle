<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Unit\Event\Server;

use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\ClassDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\PhpParserGenerators\FileBuilder;
use PhpParser\Builder\Use_;
use PhpParser\Node\Stmt\Use_ as UseStmt;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OnMoon\OpenApiServerBundle\CodeGenerator\PhpParserGenerators\FileBuilder
 */
class FileBuilderTest extends TestCase
{
    private FileBuilder $fileBuilder;

    public function testAddStmt(): void
    {
        $fqcn            = 'NamespaceOne\NamespaceTwo\ClassDefinitionOne';
        $classDefinition = ClassDefinition::fromFQCN($fqcn);

        $this->fileBuilder = new FileBuilder($classDefinition);

        $stmt = new Use_('test', UseStmt::TYPE_NORMAL);
        $this->fileBuilder->addStmt($stmt);
        Assert::assertEquals('test', $this->fileBuilder->getNamespace()->getNode()->stmts[0]->uses[0]->name->parts[0]);
    }

    public function testReferenceWithNotMatching(): void
    {
        $fqcn            = 'NamespaceOne\NamespaceTwo\ClassDefinitionOne';
        $classDefinition = ClassDefinition::fromFQCN($fqcn);

        $fqcnTwo            = 'NamespaceOne\NamespaceThree\ClassDefinitionTwo';
        $classDefinitionTwo = ClassDefinition::fromFQCN($fqcnTwo);

        $this->fileBuilder = new FileBuilder($classDefinition);
        $reference         = $this->fileBuilder->getReference($classDefinitionTwo);
        $namespace         = $this->fileBuilder->getNamespace();

        Assert::assertCount(1, $namespace->getNode()->stmts);
        Assert::assertEquals('ClassDefinitionTwo', $reference);
    }

    public function testReferenceWithSameDefinition(): void
    {
        $fqcn            = 'NamespaceOne\NamespaceTwo\ClassDefinition';
        $classDefinition = ClassDefinition::fromFQCN($fqcn);

        $this->fileBuilder = new FileBuilder($classDefinition);
        $reference         = $this->fileBuilder->getReference($classDefinition);
        $namespace         = $this->fileBuilder->getNamespace();

        Assert::assertEquals('NamespaceOne', $namespace->getNode()->name->parts[0]);
        Assert::assertEquals('NamespaceTwo', $namespace->getNode()->name->parts[1]);

        Assert::assertEquals('ClassDefinition', $reference);
    }

    public function testRenameDefault(): void
    {
        $fqcn            = 'NamespaceOne\NamespaceThree\ClassDefinition';
        $classDefinition = ClassDefinition::fromFQCN($fqcn);

        $fqcnTwo            = 'NamespaceOne\NamespaceTwo\ClassDefinition';
        $classDefinitionTwo = ClassDefinition::fromFQCN($fqcnTwo);

        $this->fileBuilder = new FileBuilder($classDefinition);
        $reference         = $this->fileBuilder->getReference($classDefinitionTwo);
        $namespace         = $this->fileBuilder->getNamespace();

        Assert::assertCount(1, $namespace->getNode()->stmts);
        Assert::assertEquals('ClassDefinition_', $namespace->getNode()->stmts[0]->uses[0]->alias->name);
        Assert::assertEquals('ClassDefinition_', $reference);
    }

    public function testRenameNumericIncrements(): void
    {
        $fqcn            = 'NamespaceOne\NamespaceThree\_0123';
        $classDefinition = ClassDefinition::fromFQCN($fqcn);

        $fqcnTwo            = 'NamespaceOne\NamespaceTwo\_0123';
        $classDefinitionTwo = ClassDefinition::fromFQCN($fqcnTwo);

        $this->fileBuilder = new FileBuilder($classDefinition);
        $reference         = $this->fileBuilder->getReference($classDefinitionTwo);

        Assert::assertEquals('_124', $reference);
    }

    public function testRenameWithUnderscoreAtTheEnd(): void
    {
        $fqcn            = 'NamespaceOne\NamespaceThree\ClassDefinition_';
        $classDefinition = ClassDefinition::fromFQCN($fqcn);

        $fqcnTwo            = 'NamespaceOne\NamespaceTwo\ClassDefinition_';
        $classDefinitionTwo = ClassDefinition::fromFQCN($fqcnTwo);

        $this->fileBuilder = new FileBuilder($classDefinition);
        $reference         = $this->fileBuilder->getReference($classDefinitionTwo);

        Assert::assertEquals('ClassDefinition_1', $reference);
    }
}
