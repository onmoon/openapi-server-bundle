<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Functional\Command;

use OnMoon\OpenApiServerBundle\Command\ProcessFactory;
use OnMoon\OpenApiServerBundle\Command\RefreshApiCodeCommand;
use OnMoon\OpenApiServerBundle\Test\Functional\TestKernel;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Tester\CommandTester;

use function rtrim;
use function Safe\sprintf;

/** @covers  \OnMoon\OpenApiServerBundle\Command\RefreshApiCodeCommand */
class RefreshApiCodeCommandTest extends CommandTestCase
{
    private const COMMAND = 'open-api:refresh';

    /** @var ProcessFactory|MockObject  */
    private $processFactory;

    public function setUp(): void
    {
        parent::setUp();

        $this->processFactory = $this->createMock(ProcessFactory::class);
    }

    public function tearDown(): void
    {
        unset($this->processFactory);
        parent::tearDown();
    }

    public function testRefreshingAcceptedByUser(): void
    {
        $this->processFactory
            ->expects(self::atLeast(2))
            ->method('getProcess');

        $command = new RefreshApiCodeCommand(TestKernel::$bundleRootPath, $this->processFactory, self::COMMAND);
        $this->application->add($command);
        $this->commandTester = new CommandTester($command);

        $this->commandTester->setInputs(['y']);

        $this->commandTester->execute([
            'command'  => self::COMMAND,
        ]);

        $output = $this->commandTester->getDisplay();
        Assert::assertEquals(sprintf('Delete all contents of the directory %s? (y/n):', TestKernel::$bundleRootPath), rtrim($output));
        Assert::assertSame(0, $this->commandTester->getStatusCode());
    }

    public function testRefreshingDeclinedByUser(): void
    {
        $this->processFactory
            ->expects(self::never())
            ->method('getProcess');

        $command = new RefreshApiCodeCommand(TestKernel::$bundleRootPath, $this->processFactory, self::COMMAND);
        $this->application->add($command);
        $this->commandTester = new CommandTester($command);

        $this->commandTester->setInputs(['n']);

        $this->commandTester->execute([
            'command'  => self::COMMAND,
        ]);

        $output = $this->commandTester->getDisplay();
        Assert::assertEquals(sprintf('Delete all contents of the directory %s? (y/n):', TestKernel::$bundleRootPath), rtrim($output));
        Assert::assertSame(0, $this->commandTester->getStatusCode());
    }
}
