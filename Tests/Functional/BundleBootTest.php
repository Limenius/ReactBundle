<?php

namespace Limenius\ReactBundle\Tests\Functional;

use Limenius\ReactRenderer\Twig\ReactRenderExtension;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelInterface;

class BundleBootTest extends KernelTestCase
{
    protected static function createKernel(array $options = []): KernelInterface
    {
        return new TestKernel('test', false, $options['react_config'] ?? []);
    }

    public function testKernelBootsWithDefaultConfig(): void
    {
        $kernel = self::bootKernel();

        $this->assertArrayHasKey('LimeniusReactBundle', $kernel->getBundles());
        $this->assertTrue($this->getTwig()->hasExtension(ReactRenderExtension::class));
    }

    /**
     * @dataProvider configProvider
     */
    public function testKernelBootsWithConfig(array $config): void
    {
        self::bootKernel(['react_config' => $config]);

        $this->assertTrue($this->getTwig()->hasExtension(ReactRenderExtension::class));
    }

    public static function configProvider(): iterable
    {
        yield 'client side' => [['default_rendering' => 'client_side']];
        yield 'external server' => [['serverside_rendering' => [
            'mode' => 'external_server',
            'server_socket_path' => '/tmp/node.sock',
        ]]];
        yield 'phpexecjs' => [['serverside_rendering' => [
            'mode' => 'phpexecjs',
            'server_bundle_path' => '/tmp/server-bundle.js',
        ]]];
        yield 'cache enabled' => [['serverside_rendering' => [
            'cache' => ['enabled' => true, 'key' => 'test'],
        ]]];
    }

    public function testRendersClientSideComponent(): void
    {
        self::bootKernel();
        $this->getService('request_stack')->push(Request::create('http://localhost/'));

        $html = $this->getTwig()
            ->createTemplate("{{ react_component('X', {rendering: 'client_side', props: {a: 1}}) }}")
            ->render([]);

        $this->assertStringContainsString('data-component-name="X"', $html);
        $this->assertStringContainsString('>{"a":1}</script>', $html);
    }

    private function getTwig(): \Twig\Environment
    {
        return $this->getService('twig');
    }

    private function getService(string $id)
    {
        $container = method_exists(self::class, 'getContainer') ? self::getContainer() : self::$container;

        return $container->get($id);
    }
}
