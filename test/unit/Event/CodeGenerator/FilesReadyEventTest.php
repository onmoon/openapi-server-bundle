<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Unit\Event\CodeGenerator;

use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\GeneratedClassDefinition;
use OnMoon\OpenApiServerBundle\CodeGenerator\Definitions\GeneratedFileDefinition;
use OnMoon\OpenApiServerBundle\Event\CodeGenerator\FilesReadyEvent;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

/** @covers \OnMoon\OpenApiServerBundle\Event\CodeGenerator\FilesReadyEvent */
final class FilesReadyEventTest extends TestCase
{
    public function testFilesReadyEventTestReturnFiles(): void
    {
        $generatedClassDefinition = $this->createMock(GeneratedClassDefinition::class);
        $generatedFileDefinition  = new GeneratedFileDefinition($generatedClassDefinition, 'fileContents');

        $files = [$generatedFileDefinition];

        $filesReadyEvent = new FilesReadyEvent($files);

        Assert::assertEquals($files, $filesReadyEvent->files());
    }
}
