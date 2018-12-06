<?php

namespace Limenius\ReactBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class CacheCompilerPass implements CompilerPassInterface
{
    private $key;

    public function process(ContainerBuilder $container)
    {
        if (!$container->getParameter('limenius_react.cache_enabled')) {
            return;
        }
        $appCache = $container->findDefinition('cache.app');
        $key = $container->getParameter('limenius_react.cache_key');
        $renderer = $container
            ->getDefinition('limenius_react.phpexecjs_react_renderer')
            ->addMethodCall('setCache', [$appCache, $key]);

        $container->getDefinition('limenius_react.static_react_renderer')
            ->addMethodCall('setCache', [$appCache, $key]);
    }
}
