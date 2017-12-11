<?php

namespace Limenius\ReactBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use App\DependencyInjection\Compiler\CacheCompilerPass;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class LimeniusReactExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);


        $container->setParameter('limenius_react.default_rendering', $config['default_rendering']);
        $container->setParameter('limenius_react.fail_loud', $config['serverside_rendering']['fail_loud']);
        $container->setParameter('limenius_react.trace', $config['serverside_rendering']['trace']);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');
        $loader->load('twig.xml');

        $serverSideEnabled = $config['default_rendering'];
        if (in_array($serverSideEnabled, array('both', 'server_side'), true)) {
            $serverSideMode = $config['serverside_rendering']['mode'];
            if ($serverSideMode === 'external_server') {
                if ($serverSocketPath = $config['serverside_rendering']['server_socket_path']) {
                    $container
                        ->getDefinition('limenius_react.external_react_renderer')
                        ->addMethodCall('setServerSocketPath', array($serverSocketPath))
                        ;
                }
                $renderer = $container->getDefinition('limenius_react.external_react_renderer');
            } else {
                if ($serverBundlePath = $config['serverside_rendering']['server_bundle_path']) {
                    $container
                        ->getDefinition('limenius_react.phpexecjs_react_renderer')
                        ->addMethodCall('setServerBundlePath', array($serverBundlePath))
                        ;
                }
                $renderer = $container->getDefinition('limenius_react.phpexecjs_react_renderer');
            }
            $container->setDefinition('limenius_react.react_renderer', $renderer);
        }

        $cache = $config['serverside_rendering']['cache'];
        $container->setParameter('limenius_react.cache_enabled', $cache['enabled']);
        if ($cache['enabled']) {
            $container->setParameter('limenius_react.cache_key', $cache['key']);
        }
    }
}
