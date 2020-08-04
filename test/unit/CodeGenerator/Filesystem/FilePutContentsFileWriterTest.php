<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Unit\CodeGenerator\Filesystem;

use OnMoon\OpenApiServerBundle\CodeGenerator\Filesystem\FilePutContentsFileWriter;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

use function Safe\file_get_contents;
use function Safe\rmdir;
use function Safe\unlink;

use const DIRECTORY_SEPARATOR;

/**
 * @covers \OnMoon\OpenApiServerBundle\Test\Unit\CodeGenerator\Filesystem\FilePutContentsFileWriter
 */
final class FilePutContentsFileWriterTest extends TestCase
{
    public function testWriteCreatesFile(): void
    {
        $path        = 'someRandomDir';
        $filename    = 'testFilename.txt';
        $fullPath    = $path . DIRECTORY_SEPARATOR . $filename;
        $fileContent = 'Some Random Content';

        $fileWriter = new FilePutContentsFileWriter(0755);

        Assert::assertFileDoesNotExist($fullPath);
        Assert::assertDirectoryDoesNotExist($path);

        $fileWriter->write($path, $filename, $fileContent);

        Assert::assertDirectoryExists($path);
        Assert::assertFileExists($fullPath);
        Assert::assertEquals($fileContent, file_get_contents($fullPath));

        unlink($fullPath);
        rmdir($path);
    }
}
