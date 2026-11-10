<?php

namespace Limenius\ReactBundle\Tests\DependencyInjection;

use Limenius\ReactBundle\DependencyInjection\Compiler\CacheCompilerPass;
use Limenius\ReactBundle\DependencyInjection\LimeniusReactExtension;
use Limenius\ReactBundle\LimeniusReactBundle;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class LimeniusReactExtensionTest extends TestCase
{
    public function testDefaultConfigRegistersPhpExecJsRenderer(): void
    {
        $container = $this->load([]);

        $this->assertTrue($container->hasDefinition('limenius_react.render_extension'));
        $this->assertTrue($container->hasDefinition('limenius_react.react_renderer'));
        $this->assertSame(
            $container->getDefinition('limenius_react.phpexecjs_react_renderer'),
            $container->getDefinition('limenius_react.react_renderer')
        );
        $this->assertSame('both', $container->getParameter('limenius_react.default_rendering'));
        $this->assertFalse($container->getParameter('limenius_react.cache_enabled'));
    }

    public function testClientSideRenderingDoesNotRegisterRenderer(): void
    {
        $container = $this->load(['default_rendering' => 'client_side']);

        $this->assertFalse($container->hasDefinition('limenius_react.react_renderer'));
        $this->assertTrue($container->hasDefinition('limenius_react.render_extension'));
    }

    public function testExternalServerMode(): void
    {
        $container = $this->load(['serverside_rendering' => [
            'mode' => 'external_server',
            'server_socket_path' => 'unix:///tmp/node.sock',
        ]]);

        $renderer = $container->getDefinition('limenius_react.react_renderer');
        $this->assertSame($container->getDefinition('limenius_react.external_react_renderer'), $renderer);
        $this->assertSame(
            [['setServerSocketPath', ['unix:///tmp/node.sock']]],
            $renderer->getMethodCalls()
        );
    }

    public function testPhpExecJsModeWithServerBundlePath(): void
    {
        $container = $this->load(['serverside_rendering' => [
            'mode' => 'phpexecjs',
            'server_bundle_path' => '/tmp/server-bundle.js',
        ]]);

        $this->assertSame(
            [['setServerBundlePath', ['/tmp/server-bundle.js']]],
            $container->getDefinition('limenius_react.react_renderer')->getMethodCalls()
        );
    }

    public function testCacheCompilerPassAddsCacheCalls(): void
    {
        $container = $this->load(['serverside_rendering' => [
            'cache' => ['enabled' => true, 'key' => 'my_key'],
        ]]);
        $appCache = new Definition(\stdClass::class);
        $container->setDefinition('cache.app', $appCache);

        (new CacheCompilerPass())->process($container);

        $this->assertSame('my_key', $container->getParameter('limenius_react.cache_key'));
        $this->assertSame(
            [['setCache', [$appCache, 'my_key']]],
            $container->getDefinition('limenius_react.phpexecjs_react_renderer')->getMethodCalls()
        );
        $this->assertSame(
            [['setCache', [$appCache]]],
            $container->getDefinition('limenius_react.render_extension')->getMethodCalls()
        );
    }

    public function testCacheCompilerPassDoesNothingWhenDisabled(): void
    {
        $container = $this->load([]);

        (new CacheCompilerPass())->process($container);

        $this->assertSame([], $container->getDefinition('limenius_react.render_extension')->getMethodCalls());
    }

    public function testBundleRegistersCacheCompilerPass(): void
    {
        $container = new ContainerBuilder();
        (new LimeniusReactBundle())->build($container);

        $passes = array_filter(
            $container->getCompilerPassConfig()->getPasses(),
            function ($pass) {
                return $pass instanceof CacheCompilerPass;
            }
        );
        $this->assertCount(1, $passes);
    }

    private function load(array $config): ContainerBuilder
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.project_dir', __DIR__);
        (new LimeniusReactExtension())->load([$config], $container);

        return $container;
    }
}
