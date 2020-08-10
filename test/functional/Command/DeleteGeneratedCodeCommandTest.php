<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Functional\Command;

use OnMoon\OpenApiServerBundle\Command\DeleteGeneratedCodeCommand;
use PHPUnit\Framework\Assert;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;

use function Safe\file_put_contents;
use function Safe\sprintf;
use function Safe\mkdir;

/**
 * @covers \OnMoon\OpenApiServerBundle\Command\DeleteGeneratedCodeCommand
 */
class DeleteGeneratedCodeCommandTest extends CommandTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $command             = $this->application->find(DeleteGeneratedCodeCommand::COMMAND);
        $this->commandTester = new CommandTester($command);

        mkdir($this->pathForFileGeneration);
        file_put_contents($this->pathForFileGeneration . '/test.txt', '');
    }

    public function tearDown(): void
    {
        $filesystem = new Filesystem();
        $filesystem->remove([$this->pathForFileGeneration]);

        parent::tearDown();
    }

    public function testDeletion(): void
    {
        $this->commandTester->setInputs(['y']);

        $this->commandTester->execute([
            'command'  => DeleteGeneratedCodeCommand::COMMAND,
        ]);

        $output = $this->commandTester->getDisplay();
        Assert::assertEquals(sprintf('Delete all contents of the directory %1$s? (y/n): All contents of directory were deleted: %1$s' . "\n", $this->pathForFileGeneration), $output);
        Assert::assertFileDoesNotExist($this->pathForFileGeneration . '/test.txt');
    }

    public function testDeletionCancel(): void
    {
        $this->commandTester->setInputs(['n']);

        $this->commandTester->execute([
            'command'  => DeleteGeneratedCodeCommand::COMMAND,
        ]);

        $output = $this->commandTester->getDisplay();
        Assert::assertEquals(sprintf('Delete all contents of the directory %1$s? (y/n): ', $this->pathForFileGeneration), $output);
        Assert::assertDirectoryExists($this->pathForFileGeneration);
        Assert::assertFileExists($this->pathForFileGeneration . '/test.txt');
    }
}
