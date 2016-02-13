<?php

namespace Limenius\ReactBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('limenius_react');

        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('limenius_filesystem_router');
        $rootNode
            ->children()
                ->enumNode('default_rendering')
                    ->values(array('only_serverside', 'only_clientside', 'both'))
                    ->defaultValue('both')
                ->end()
                ->arrayNode('serverside_rendering')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('fail_loud')
                            ->defaultFalse()
                        ->end()
                        ->booleanNode('trace')
                            ->defaultFalse()
                        ->end()
                        ->scalarNode('node_binary_path')
                            ->defaultNull()
                        ->end()
                        ->scalarNode('server_bundle_path')
                            ->defaultNull()
                        ->end()
                    ->end()
                ->end()
            ->end();
        return $treeBuilder;
    }
}

