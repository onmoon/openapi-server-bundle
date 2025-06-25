<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Functional\Command;

use OnMoon\OpenApiServerBundle\Command\DeleteGeneratedCodeCommand;
use OnMoon\OpenApiServerBundle\Test\Functional\TestKernel;
use PHPUnit\Framework\Assert;
use Symfony\Component\Console\Tester\CommandTester;

use function is_dir;
use function rtrim;
use function Safe\file_put_contents;
use function Safe\mkdir;
use function sprintf;

/** @covers \OnMoon\OpenApiServerBundle\Command\DeleteGeneratedCodeCommand */
class DeleteGeneratedCodeCommandTest extends CommandTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $command             = $this->application->find(DeleteGeneratedCodeCommand::COMMAND);
        $this->commandTester = new CommandTester($command);

        if (! is_dir(TestKernel::$bundleRootPath)) {
            mkdir(TestKernel::$bundleRootPath);
        }

        file_put_contents(TestKernel::$bundleRootPath . '/test.txt', '');
    }

    public function testDeletion(): void
    {
        $this->commandTester->setInputs(['y']);

        $this->commandTester->execute([
            'command'  => DeleteGeneratedCodeCommand::COMMAND,
        ]);

        $output = $this->commandTester->getDisplay();
        Assert::assertEquals(sprintf('Delete all contents of the directory %1$s? (y/n): All contents of directory were deleted: %1$s', TestKernel::$bundleRootPath), rtrim($output));
        Assert::assertSame(0, $this->commandTester->getStatusCode());
        Assert::assertFileDoesNotExist(TestKernel::$bundleRootPath . '/test.txt');
    }

    public function testDeletionCancel(): void
    {
        $this->commandTester->setInputs(['n']);

        $this->commandTester->execute([
            'command'  => DeleteGeneratedCodeCommand::COMMAND,
        ]);

        $output = $this->commandTester->getDisplay();
        Assert::assertEquals(sprintf('Delete all contents of the directory %1$s? (y/n):', TestKernel::$bundleRootPath), rtrim($output));
        Assert::assertSame(0, $this->commandTester->getStatusCode());
        Assert::assertDirectoryExists(TestKernel::$bundleRootPath);
        Assert::assertFileExists(TestKernel::$bundleRootPath . '/test.txt');
    }
}
