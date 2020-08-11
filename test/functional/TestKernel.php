<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\Test\Functional;

use OnMoon\OpenApiServerBundle\OpenApiServerBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

class TestKernel extends BaseKernel
{
    use MicroKernelTrait;

    public static string $bundleRootPath      = __DIR__ . '/Generated';
    public static string $bundleRootNamespace = __NAMESPACE__ . '\Generated';

    protected function build(ContainerBuilder $container): void
    {
        $specificationName = 'petstore';
        $specification     = [
            'path' => '%kernel.project_dir%/test/functional/openapi_specification.yaml',
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

    protected function configureContainer(ContainerConfigurator $container): void
    {
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        $routes->import(__DIR__ . '/routes.yaml');
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
}