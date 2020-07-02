<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Command;

use OnMoon\OpenApiServerBundle\CodeGenerator\ApiServerCodeGenerator;
use OnMoon\OpenApiServerBundle\Specification\SpecificationLoader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

use function Safe\sprintf;

class GenerateApiCodeCommand extends Command
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

    protected function configure(): void
    {
        $this->setDescription('Generates API server code');
    }

    /**
     * phpcs:disable SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     * @var string
     */
    protected static $defaultName = self::COMMAND;

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->cache->invalidateTags([SpecificationLoader::CACHE_TAG]);
        $this->apiServerCodeGenerator->generate();

        $output->writeln(sprintf('API server code generated in: %s', $this->rootPath));

        return 0;
    }
}
