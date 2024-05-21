<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Functional;

use OnMoon\OpenApiServerBundle\OpenApiServerBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

use const DIRECTORY_SEPARATOR;

abstract class TestKernel extends BaseKernel
{
    use MicroKernelTrait;

    public static string $bundleRootPath      = __DIR__ . DIRECTORY_SEPARATOR . 'Generated';
    public static string $bundleRootNamespace = __NAMESPACE__ . '\Generated';

    protected function build(ContainerBuilder $container): void
    {
        $specificationName = 'petstore';
        $specification     = [
            'path' => __DIR__ . DIRECTORY_SEPARATOR . 'openapi_specification.yaml',
            'type' => 'yaml',
            'name_space' => 'PetStore',
            'media_type' => 'application/json',
        ];
        $container->prependExtensionConfig('open_api_server', [
            'root_path' => self::$bundleRootPath,
            'root_name_space' => self::$bundleRootNamespace,
            'language_level' => '7.4.0',
            'generated_dir_permissions' => 0755,
            'full_doc_blocks' => false,
            'send_nulls' => false,
            'specs' => [$specificationName => $specification],
        ]);

        $container->prependExtensionConfig(
            'framework',
            ['test' => true]
        );
    }

    public function getCacheDir(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'cache';
    }

    public function getLogDir(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'log';
    }

    /**
     * {@inheritDoc}
     */
    public function registerBundles(): iterable
    {
        $contents = [
            FrameworkBundle::class => ['test' => true],
            OpenApiServerBundle::class => ['test' => true],
        ];
        foreach ($contents as $class => $envs) {
            if (! ($envs[$this->environment] ?? false)) {
                continue;
            }

            yield new $class();
        }
    }

    public function shutdown(): void
    {
        $filesystem = new Filesystem();
        $filesystem->remove([__DIR__ . DIRECTORY_SEPARATOR . 'var']);

        parent::shutdown();
    }
}
