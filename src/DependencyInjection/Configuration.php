<?php

declare(strict_types=1);

namespace OnMoon\OpenApiServerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('open_api_server');

        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('root_path')->end()
                ->scalarNode('root_name_space')->defaultValue('App\Generated')->cannotBeEmpty()->end()
                ->scalarNode('language_level')->defaultValue('8.0.0')->cannotBeEmpty()->end()
                ->scalarNode('generated_dir_permissions')->defaultValue('0755')->cannotBeEmpty()->end()
                ->booleanNode('full_doc_blocks')->defaultValue(false)->end()
                ->booleanNode('send_nulls')->defaultValue(false)->end()
                ->arrayNode('specs')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('path')->isRequired()->cannotBeEmpty()->end()
                            ->enumNode('type')->values(['yaml', 'json'])->end()
                            ->scalarNode('name_space')->isRequired()->cannotBeEmpty()->end()
                            ->enumNode('media_type')
                                ->values(['application/json'])
                                ->isRequired()
                                ->cannotBeEmpty()
                            ->end()
                        ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
