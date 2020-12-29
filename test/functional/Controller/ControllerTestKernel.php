<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Functional\Controller;

use OnMoon\OpenApiServerBundle\OpenApiServerBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

class ControllerTestKernel extends BaseKernel
{
    use MicroKernelTrait;

    public static string $bundleRootPath      = __DIR__ . '/Generated';
    public static string $bundleRootNamespace = __NAMESPACE__ . '\Generated';

    /**
     * @param ContainerBuilder|ContainerConfigurator $container
     */
    protected function build($container): void
    {
        $specificationName = 'petstore';
        $specification     = [
            'path' => __DIR__ . '/openapi_specification.yaml',
            'type' => 'yaml',
            'name_space' => 'PetStore',
            'media_type' => 'application/json',
        ];
        $container->prependExtensionConfig('open_api_server', [
            'root_path' => static::$bundleRootPath,
            'root_name_space' => static::$bundleRootNamespace,
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

    /**
     * @inheritDoc
     */
    public function getCacheDir()
    {
        return __DIR__ . '/var/cache';
    }

    /**
     * @inheritDoc
     */
    public function getLogDir()
    {
        return __DIR__ . '/var/log';
    }

    /**
     * @param ContainerBuilder|ContainerConfigurator $container
     */
    protected function configureContainer($container): void
    {
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        $routes->import(__DIR__ . '/openapi_routes.yaml');
    }

    /**
     * {@inheritdoc}
     */
    public function registerBundles(): iterable
    {
        $contents = [
            FrameworkBundle::class => ['test' => true],
            OpenApiServerBundle::class => ['test' => true],
        ];
        foreach ($contents as $class => $envs) {
            if (! ($envs[$this->environment] ?? $envs['all'] ?? false)) {
                continue;
            }

            yield new $class();
        }
    }

    public function shutdown(): void
    {
        $filesystem = new Filesystem();
        $filesystem->remove([__DIR__ . '/var']);

        parent::shutdown();
    }
}
