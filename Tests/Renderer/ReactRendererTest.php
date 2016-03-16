<?php
namespace Limenius\ReactBundle\Tests\Renderer;

use Limenius\ReactBundle\Renderer\ReactRenderer;
use Psr\Log\LoggerInterface;
use Nacmartin\PhpExecJs\PhpExecJs;

class RendererTest extends \PHPUnit_Framework_TestCase
{
    private $renderer;

    public function setUp()
    {
        $this->logger = $this->getMockBuilder(LoggerInterface::class)
            ->getMock();
        $this->phpExecJs = $this->getMockBuilder(PhpExecJs::class)
            ->getMock();
        $this->phpExecJs->method('evalJs')
             ->willReturn('{ "html" : "go for it", "hasErrors" : false, "consoleReplayScript": " - my replay"}');
        $this->renderer = new ReactRenderer($this->logger, $this->phpExecJs, __DIR__.'/Fixtures/server-bundle.js');
    }

    /**
     * @expectedException RuntimeException
     */
    public function testServerBundleNotFound()
    {
        $this->renderer = new ReactRenderer($this->logger, $this->phpExecJs, __DIR__.'/Fixtures/i-dont-exist.js');
        $this->renderer->render('MyApp', 'props', 1, null, false);
    }

    public function testPlus()
    {
        $this->assertEquals('go for it - my replay', $this->renderer->render('MyApp', 'props', 1, null, false));
    }

    public function testWithStoreData()
    {
        $this->assertEquals('go for it - my replay', $this->renderer->render('MyApp', 'props', 1, array('Store' => '{foo:"bar"'), false));
    }

    /**
     * @expectedException Limenius\ReactBundle\Exception\EvalJsException
     */
    public function testFailLoud()
    {
        $phpExecJs = $this->getMockBuilder(PhpExecJs::class)
            ->getMock();
        $phpExecJs->method('evalJs')
             ->willReturn('{ "html" : "go for it", "hasErrors" : true, "consoleReplayScript": " - my replay"}');
        $this->renderer = new ReactRenderer($this->logger, $phpExecJs, __DIR__.'/Fixtures/server-bundle.js', true);
        $this->renderer->render('MyApp', 'props', 1, null, true);
    }
}
