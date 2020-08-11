<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Functional\Command;

use OnMoon\OpenApiServerBundle\Command\ProcessFactory;
use OnMoon\OpenApiServerBundle\Command\RefreshApiCodeCommand;
use OnMoon\OpenApiServerBundle\Test\Functional\TestKernel;
use PHPUnit\Framework\Assert;
use Symfony\Component\Console\Tester\CommandTester;

use function rtrim;
use function Safe\sprintf;

/** @covers  \OnMoon\OpenApiServerBundle\Command\RefreshApiCodeCommand */
class RefreshApiCodeCommandTest extends CommandTestCase
{
    private const COMMAND = 'open-api:refresh';

    public function setUp(): void
    {
        parent::setUp();

        $processFactory = $this->createMock(ProcessFactory::class);
        $processFactory
            ->expects(self::atLeast(2))
            ->method('getProcess');

        $command = new RefreshApiCodeCommand(TestKernel::$bundleRootPath, $processFactory, self::COMMAND);
        $this->application->add($command);
        $this->commandTester = new CommandTester($command);
    }

    public function testRefreshing(): void
    {
        $this->commandTester->setInputs(['y']);

        $this->commandTester->execute([
            'command'  => self::COMMAND,
        ]);

        $output = $this->commandTester->getDisplay();
        Assert::assertEquals(sprintf('Delete all contents of the directory %s? (y/n):', $this->pathForFileGeneration), rtrim($output));
    }
}
