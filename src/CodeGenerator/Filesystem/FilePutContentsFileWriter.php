<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\CodeGenerator\Filesystem;

use function intval;
use function is_dir;
use function Safe\file_put_contents;
use function Safe\mkdir;

use const DIRECTORY_SEPARATOR;

final class FilePutContentsFileWriter implements FileWriter
{
    private int $dirPemissions;

    public function __construct(string $dirPemissions)
    {
        $this->dirPemissions = intval($dirPemissions, 8);
    }

    public function write(string $path, string $filename, string $contents): void
    {
        if (! is_dir($path)) {
            mkdir($path, $this->dirPemissions, true);
        }

        file_put_contents($path . DIRECTORY_SEPARATOR . $filename, $contents);
    }
}
