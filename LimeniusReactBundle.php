<?php

namespace Limenius\ReactBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Limenius\ReactBundle\DependencyInjection\Compiler\CacheCompilerPass;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class LimeniusReactBundle
 */
class LimeniusReactBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new CacheCompilerPass());
    }
}
