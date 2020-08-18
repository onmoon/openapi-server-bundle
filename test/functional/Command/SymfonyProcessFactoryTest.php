<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Functional\Command;

use OnMoon\OpenApiServerBundle\Command\SymfonyProcessFactory;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

class SymfonyProcessFactoryTest extends TestCase
{
    public function testGetProcessWithGoodArguments(): void
    {
        $symfonyProcessFactory = new SymfonyProcessFactory();
        $args                  = ['php', 'bin/console', 'testCommand'];
        $expectedProcess       = new Process($args);
        $process               = $symfonyProcessFactory->getProcess($args);
        Assert::assertSame($expectedProcess->getCommandLine(), $process->getCommandLine());
    }
}
