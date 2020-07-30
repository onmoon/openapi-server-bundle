<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Generation;

use InvalidArgumentException;
use OnMoon\OpenApiServerBundle\CodeGenerator\Filesystem\FileWriter;

use function array_key_exists;
use function rtrim;

use const DIRECTORY_SEPARATOR;

final class InMemoryFileWriter implements FileWriter
{
    /**
     * @var string[]
     * @psalm-var array<string, string>
     */
    private array $files = [];

    public function write(string $path, string $filename, string $contents): void
    {
        $this->files[rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename] = $contents;
    }

    public function getContentsByFullPath(string $fullPath): string
    {
        $fullPath = '/' . ltrim(str_replace('/', DIRECTORY_SEPARATOR, $fullPath), '\\/');

        if (! array_key_exists($fullPath, $this->files)) {
            throw new InvalidArgumentException('No file was generated with path: ' . $fullPath);
        }

        return $this->files[$fullPath];
    }

    /**
     * @return string[]
     *
     * @psalm-return array<string, string>
     */
    public function getAll(): array
    {
        return $this->files;
    }
}
