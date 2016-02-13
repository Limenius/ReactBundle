<?php

namespace Limenius\ReactBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

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

        if ($nodeBinaryPath = $config['serverside_rendering']['node_binary_path']) {
            $container
                ->getDefinition('limenius_react.phpexecjs')
                ->addArgument($nodeBinaryPath)
                ;
        }
        if ($serverBundlePath = $config['serverside_rendering']['server_bundle_path']) {
            $container
                ->getDefinition('limenius_react.react_renderer')
                ->addMethodCall('setServerBundlePath', array($serverBundlePath))
                ;

        }

    }
}
