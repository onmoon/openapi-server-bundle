<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Command;

use FilesystemIterator;
use OnMoon\OpenApiServerBundle\CodeGenerator\ApiServerCodeGenerator;
use OnMoon\OpenApiServerBundle\Specification\SpecificationLoader;
use Override;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

use function in_array;
use function is_dir;
use function iterator_count;
use function Safe\rmdir;
use function Safe\unlink;
use function sprintf;

#[AsCommand(name: 'open-api:generate')]
final class GenerateApiCodeCommand extends Command
{
    public const COMMAND = 'open-api:generate';

    private ApiServerCodeGenerator $apiServerCodeGenerator;
    private TagAwareCacheInterface $cache;
    private string $rootPath;

    public function __construct(
        ApiServerCodeGenerator $apiServerCodeGenerator,
        TagAwareCacheInterface $cache,
        string $rootPath,
        ?string $name = null
    ) {
        $this->apiServerCodeGenerator = $apiServerCodeGenerator;
        $this->cache                  = $cache;
        $this->rootPath               = $rootPath;

        parent::__construct($name);
    }

    #[Override]
    protected function configure(): void
    {
        $this
            ->setDescription('Generates API server code')
            ->addOption(
                'keep',
                'k',
                InputOption::VALUE_NONE,
                'Keep files that are no longer part of specification'
            );
    }

    /**
     * phpcs:disable SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     *
     * @var string|null
     */
    protected static $defaultName = self::COMMAND;

    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $keep = (bool) $input->getOption('keep');

        $this->cache->invalidateTags([SpecificationLoader::CACHE_TAG]);
        $files = $this->apiServerCodeGenerator->generate();

        if (! $keep) {
            $this->removeExtraFiles($this->rootPath, $files);
            $this->removeEmptyDirectories($this->rootPath);
        }

        $output->writeln(sprintf('API server code generated in: %s', $this->rootPath));

        return 0;
    }

    /** @param string[] $generatedFiles */
    private function removeExtraFiles(string $root, array $generatedFiles): void
    {
        if (! is_dir($root)) {
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $root,
                FilesystemIterator::SKIP_DOTS
            ),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        /** @var SplFileInfo[] $iterator */
        foreach ($iterator as $directoryOrFile) {
            if ($directoryOrFile->isDir() || in_array($directoryOrFile->getPathname(), $generatedFiles, true)) {
                continue;
            }

            unlink($directoryOrFile->getPathname());
        }
    }

    private function removeEmptyDirectories(string $root): void
    {
        if (! is_dir($root)) {
            return;
        }

        $directories = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        /**
         * @var SplFileInfo $dir
         * @psalm-suppress PossiblyNullArgument
         */
        foreach ($directories as $dir) {
            if (! $dir->isDir() || iterator_count($directories->callGetChildren()) !== 0) {
                continue;
            }

            rmdir($dir->getPathname());
        }
    }
}
