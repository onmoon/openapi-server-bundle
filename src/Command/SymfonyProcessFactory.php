<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Command;

use Symfony\Component\Process\Process;

final class SymfonyProcessFactory implements ProcessFactory
{
    /**
     * @param string[] $arguments
     */
    public function getProcess(array $arguments): Process
    {
        return new Process($arguments);
    }
}
