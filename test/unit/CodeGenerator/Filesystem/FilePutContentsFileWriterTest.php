<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Unit\CodeGenerator\Filesystem;

use OnMoon\OpenApiServerBundle\CodeGenerator\Filesystem\FilePutContentsFileWriter;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

use function Safe\unlink;

use const DIRECTORY_SEPARATOR;

/**
 * @covers \OnMoon\OpenApiServerBundle\Test\Unit\CodeGenerator\Filesystem\FilePutContentsFileWriter
 */
final class FilePutContentsFileWriterTest extends TestCase
{
    public function testWriteCreatesFile(): void
    {
        $path     = 'test/';
        $filename = 'testFilename.txt';
        $fullPath = $path . DIRECTORY_SEPARATOR . $filename;

        $fileWriter = new FilePutContentsFileWriter(0755);

        Assert::assertFileDoesNotExist($fullPath);
        $fileWriter->write($path, $filename, 'SomeContents');
        Assert::assertFileExists($fullPath);
        unlink($fullPath);
    }
}
