<?php

namespace Limenius\ReactBundle\Renderer;

use Nacmartin\PhpExecJs\PhpExecJs;
use Psr\Log\LoggerInterface;
use Limenius\ReactBundle\Exception\EvalJsException;

class PhpExecJsReactRenderer extends AbstractReactRenderer
{
    protected $logger;
    protected $phpExecJs;
    protected $serverBundlePath;
    protected $needToSetContext = true;
    protected $failLoud;

    public function __construct(LoggerInterface $logger, $serverBundlePath, $failLoud = false)
    {
        $this->logger = $logger;
        $this->serverBundlePath = $serverBundlePath;
        $this->failLoud = $failLoud;
    }

    public function setPhpExecJs(PhpExecJs $phpExecJs) {
        $this->phpExecJs = $phpExecJs;
    }

    public function setServerBundlePath($serverBundlePath)
    {
        $this->serverBundlePath = $serverBundlePath;
        $this->needToSetContext = true;
    }

    public function render($componentName, $propsString, $uuid, $registeredStores = array(), $trace)
    {
        $this->ensurePhpExecJsIsBuilt();
        if($this->needToSetContext){
            $this->phpExecJs->createContext($this->consolePolyfill()."\n".$this->loadServerBundle());
            $this->needToSetContext = false;
        }
        $result = json_decode($this->phpExecJs->evalJs($this->wrap($componentName, $propsString, $uuid, $registeredStores, $trace)), true);
        if ($result['hasErrors']) {
            $this->LogErrors($result['consoleReplayScript']);
            if ($this->failLoud) {
                $this->throwError($result['consoleReplayScript'], $componentName);
            }
        }
        return $result['html'].$result['consoleReplayScript'];
    }

    protected function loadServerBundle()
    {
        if (!$serverBundle = @file_get_contents($this->serverBundlePath)) {
            throw new \RuntimeException('Server bundle not found in path: '.$this->serverBundlePath);
        }
        return $serverBundle;
    }

    protected function ensurePhpExecJsIsBuilt()
    {
        if (!$this->phpExecJs) {
            $this->phpExecJs = new PhpExecJs();
        }
    }
}
