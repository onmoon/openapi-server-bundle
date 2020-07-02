<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator\Filesystem;

interface FileWriter
{
    public function write(string $path, string $filename, string $contents): void;
}
