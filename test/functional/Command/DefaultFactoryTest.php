<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Functional\Command;

use OnMoon\OpenApiServerBundle\Command\DefaultProcessFactory;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

class DefaultFactoryTest extends TestCase
{
    public function testGetProcessWithGoodArguments(): void
    {
        $defaultProcessFactory = new DefaultProcessFactory();
        $args                  = ['php', 'bin/console', 'testCommand'];
        $expectedProcess       = new Process($args);
        $process               = $defaultProcessFactory->getProcess($args);
        Assert::assertSame($expectedProcess->getCommandLine(), $process->getCommandLine());
    }
}
