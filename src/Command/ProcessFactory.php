<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Command;

use Symfony\Component\Process\Process;

interface ProcessFactory
{
    /**
     * @param string[] $arguments
     */
    public function getProcess(array $arguments): Process;
}
