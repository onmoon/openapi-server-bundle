<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Unit\CodeGenerator\Definitions;

use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\GeneratedClassDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\GeneratedFileDefinition;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

/** @covers \OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\GeneratedFileDefinition */
final class GeneratedFileDefinitionTest extends TestCase
{
    public function testGeneratedFileDefinition(): void
    {
        $generatedClassDefinition = new GeneratedClassDefinition();

        $fileContents        = 'Some file contents';
        $changedFileContents = 'Some changed file contents';

        $generatedFileDefinition = new GeneratedFileDefinition(
            $generatedClassDefinition,
            $fileContents
        );

        Assert::assertSame($generatedClassDefinition, $generatedFileDefinition->getClass());
        Assert::assertSame($fileContents, $generatedFileDefinition->getFileContents());

        $generatedFileDefinition->setFileContents($changedFileContents);

        Assert::assertSame($changedFileContents, $generatedFileDefinition->getFileContents());
    }
}
