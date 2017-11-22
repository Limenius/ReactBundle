<?php

namespace Limenius\ReactBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('limenius_react');
        $rootNode
            ->children()
                ->enumNode('default_rendering')
                    ->values(array('server_side', 'client_side', 'both'))
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
                        ->enumNode('mode')
                            ->values(array('phpexecjs', 'external_server'))
                            ->defaultValue('phpexecjs')
                        ->end()
                        ->scalarNode('server_bundle_path')
                            ->defaultNull()
                        ->end()
                        ->scalarNode('server_socket_path')
                            ->defaultNull()
                        ->end()
                        ->arrayNode('cache')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->booleanNode('enabled')
                                    ->defaultFalse()
                                ->end()
                                ->scalarNode('key')
                                    ->defaultValue('app')
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
