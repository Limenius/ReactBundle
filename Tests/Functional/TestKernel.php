<?php

namespace Limenius\ReactBundle\Tests\Functional;

use Limenius\ReactBundle\LimeniusReactBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

class TestKernel extends Kernel
{
    use MicroKernelTrait;

    /** @var array */
    private $reactConfig;

    public function __construct(string $environment, bool $debug, array $reactConfig = [])
    {
        $this->reactConfig = $reactConfig;

        parent::__construct($environment, $debug);
    }

    public function registerBundles(): iterable
    {
        return [
            new FrameworkBundle(),
            new TwigBundle(),
            new LimeniusReactBundle(),
        ];
    }

    public function getProjectDir(): string
    {
        return __DIR__;
    }

    public function getCacheDir(): string
    {
        return sys_get_temp_dir().'/limenius-react-bundle-tests/'.md5(serialize($this->reactConfig)).'/cache';
    }

    public function getLogDir(): string
    {
        return sys_get_temp_dir().'/limenius-react-bundle-tests/logs';
    }

    protected function configureContainer(ContainerConfigurator $container): void
    {
        $container->extension('framework', [
            'secret' => 'test',
            'test' => true,
            'http_method_override' => false,
        ]);
        $container->extension('twig', [
            'strict_variables' => true,
        ]);
        $container->extension('limenius_react', $this->reactConfig);
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
    }
}
