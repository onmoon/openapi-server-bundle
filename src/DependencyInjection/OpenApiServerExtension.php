<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\DependencyInjection;

use Exception;
use OnMoon\OpenApiServerBundle\Specification\SpecificationLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use function array_key_exists;
use function Safe\preg_match;
use function Safe\preg_replace;
use function str_replace;

class OpenApiServerExtension extends Extension implements ExtensionInterface
{
    /**
     * @param mixed[] $configs
     */
    public function load(array $configs, ContainerBuilder $container) : void
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');

        $configuration = new Configuration();
        /**
         * @psalm-var array{
         *     root_path?:string,
         *     root_name_space:string,
         *     language_level:string,
         *     generated_dir_permissions: string,
         *     full_doc_blocks: bool,
         *     send_nulls: bool,
         *     specs: array{
         *         path: string,
         *         type?: string,
         *         name_space: string,
         *         media_type: string
         *     }
         * } $config
         */
        $config = $this->processConfiguration($configuration, $configs);

        $rootNameSpace = $config['root_name_space'];

        if (! array_key_exists('root_path', $config) || empty($config['root_path'])) {
            if (! preg_match('|^App\\\\|', $rootNameSpace)) {
                throw new Exception('Please specify "root_path" parameter in package config if you are not ' .
                'using App namespace for generated code.');
            }

            $rootPath = preg_replace('|^App\\\\|', '%kernel.project_dir%/src/', $rootNameSpace);
            $rootPath = str_replace('\\', '/', $rootPath);
        } else {
            $rootPath = $config['root_path'];
        }

        $container->setParameter('openapi.generated.code.root.path', $rootPath);
        $container->setParameter('openapi.generated.code.root.namespace', $rootNameSpace);
        $container->setParameter('openapi.generated.code.language.level', $config['language_level']);
        $container->setParameter('openapi.generated.code.dir.permissions', $config['generated_dir_permissions']);
        $container->setParameter('openapi.generated.code.full.doc.blocks', $config['full_doc_blocks']);
        $container->setParameter('openapi.send.nulls', $config['send_nulls']);

        $definition = $container->getDefinition(SpecificationLoader::class);

        foreach ($config['specs'] as $name => $spec) {
            $definition->addMethodCall('registerSpec', [$name, $spec]);
        }
    }
}
