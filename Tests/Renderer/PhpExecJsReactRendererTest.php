<?php

namespace Limenius\ReactBundle\Tests\Renderer;

use Limenius\ReactBundle\Renderer\PhpExecJsReactRenderer;
use Psr\Log\LoggerInterface;
use Nacmartin\PhpExecJs\PhpExecJs;

/**
 * Class PhpExecJsReactRendererTest
 */
class PhpExecJsReactRendererTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PhpExecJsReactRenderer
     */
    private $renderer;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var PhpExecJs
     */
    private $phpExecJs;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->logger = $this->getMockBuilder(LoggerInterface::class)
            ->getMock();
        $this->phpExecJs = $this->getMockBuilder(PhpExecJs::class)
            ->getMock();
        $this->phpExecJs->method('evalJs')
             ->willReturn('{ "html" : "go for it", "hasErrors" : false, "consoleReplayScript": " - my replay"}');
        $this->renderer = new PhpExecJsReactRenderer($this->logger, __DIR__.'/Fixtures/server-bundle.js');
        $this->renderer->setPhpExecJs($this->phpExecJs);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testServerBundleNotFound()
    {
        $this->renderer = new PhpExecJsReactRenderer($this->logger, __DIR__.'/Fixtures/i-dont-exist.js');
        $this->renderer->render('MyApp', 'props', 1, null, false);
    }

    /**
     * Test Plus
     */
    public function testPlus()
    {
        $this->assertEquals('go for it - my replay', $this->renderer->render('MyApp', 'props', 1, null, false));
    }

    /**
     * Test with store data
     */
    public function testWithStoreData()
    {
        $this->assertEquals('go for it - my replay', $this->renderer->render('MyApp', 'props', 1, array('Store' => '{foo:"bar"'), false));
    }

    /**
     * @expectedException \Limenius\ReactBundle\Exception\EvalJsException
     */
    public function testFailLoud()
    {
        $phpExecJs = $this->getMockBuilder(PhpExecJs::class)
            ->getMock();
        $phpExecJs->method('evalJs')
             ->willReturn('{ "html" : "go for it", "hasErrors" : true, "consoleReplayScript": " - my replay"}');
        $this->renderer = new PhpExecJsReactRenderer($this->logger, __DIR__.'/Fixtures/server-bundle.js', true);
        $this->renderer->setPhpExecJs($phpExecJs);
        $this->renderer->render('MyApp', 'props', 1, null, true);
    }
}
