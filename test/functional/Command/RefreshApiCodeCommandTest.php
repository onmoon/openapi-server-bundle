<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Functional\Command;

use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;

class RefreshApiCodeCommandTest extends CommandTestCase
{
    private const COMMAND = 'open-api:refresh';

    public function setUp(): void
    {
        parent::setUp();

        $command             = $this->application->find(self::COMMAND);
        $this->commandTester = new CommandTester($command);
    }

    public function tearDown(): void
    {
        $filesystem = new Filesystem();
        $filesystem->remove([$this->pathForFileGeneration]);
        parent::tearDown();
    }
}
