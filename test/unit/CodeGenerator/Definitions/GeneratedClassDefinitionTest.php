<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Unit\CodeGenerator\Definitions;

use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\GeneratedClassDefinition;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

/** @covers \OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\GeneratedClassDefinition */
final class GeneratedClassDefinitionTest extends TestCase
{
    public function testGeneratedClassDefinition(): void
    {
        $payload = [
            'filepath' => '/some/file/path',
            'filename' => 'some_file.txt',
        ];

        $generatedClassDefinition = new GeneratedClassDefinition();
        $generatedClassDefinition->setFilePath($payload['filepath']);
        $generatedClassDefinition->setFileName($payload['filename']);

        Assert::assertSame($payload['filepath'], $generatedClassDefinition->getFilePath());
        Assert::assertSame($payload['filename'], $generatedClassDefinition->getFileName());
    }
}
