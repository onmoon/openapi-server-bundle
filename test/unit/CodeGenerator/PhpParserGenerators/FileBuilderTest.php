<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Unit\CodeGenerator\PhpParserGenerators;

use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\ClassDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\PhpParserGenerators\FileBuilder;
use PhpParser\Builder\Use_;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Use_ as UseStmt;
use PhpParser\Node\Stmt\UseUse;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/** @covers \OnMoon\OpenApiServerBundle\CodeGenerator\PhpParserGenerators\FileBuilder */
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

        /** @var \PhpParser\Node\Stmt\Use_ $statementToCheck */
        $statementToCheck = $this->fileBuilder->getNamespace()->getNode()->stmts[0];

        if ((new ReflectionClass(Name::class))->hasProperty('name')) {
            $nameSpaceName = $statementToCheck->uses[0]->name->name;
        } else {
            $nameSpaceName = $statementToCheck->uses[0]->name->parts[0];
        }

        Assert::assertEquals('test', $nameSpaceName);
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

        /** @var Name $nodeName */
        $nodeName = $namespace->getNode()->name;

        Assert::assertEquals('NamespaceOne\NamespaceTwo', $nodeName->name);
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

        /** @var \PhpParser\Node\Stmt\Use_ $useStmt */
        $useStmt = $namespace->getNode()->stmts[0];
        /** @var UseUse $useUseStmt */
        $useUseStmt = $useStmt->uses[0];
        /** @var Identifier $useUseStmtAlias */
        $useUseStmtAlias = $useUseStmt->alias;

        Assert::assertCount(1, $namespace->getNode()->stmts);
        Assert::assertEquals('ClassDefinition_', $useUseStmtAlias->name);
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

    public function testRenameNumericIncrementsWithUnderscore(): void
    {
        $fqcn            = 'NamespaceOne\NamespaceThree\_0123_345';
        $classDefinition = ClassDefinition::fromFQCN($fqcn);

        $fqcnTwo            = 'NamespaceOne\NamespaceTwo\_0123_345';
        $classDefinitionTwo = ClassDefinition::fromFQCN($fqcnTwo);

        $this->fileBuilder = new FileBuilder($classDefinition);
        $reference         = $this->fileBuilder->getReference($classDefinitionTwo);

        Assert::assertEquals('_0123_346', $reference);
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
