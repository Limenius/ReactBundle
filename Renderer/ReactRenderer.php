<?php

namespace Limenius\ReactBundle\Renderer;

use Nacmartin\PhpExecJs\PhpExecJs;
use Limenius\ReactBundle\Exception\EvalJsException;

class ReactRenderer
{
    protected $logger;
    protected $phpExecJs;
    protected $serverBundlePath;
    protected $failLoud;

    public function __construct($logger, PhpExecJs $execJs, $serverBundlePath, $failLoud = false)
    {
        $this->logger = $logger;
        $this->phpExecJs = $execJs;
        $this->serverBundlePath = $serverBundlePath;
        $this->failLoud = $failLoud;
    }

    public function setServerBundlePath($serverBundlePath)
    {
        $this->serverBundlePath = $serverBundlePath;
    }

    public function render($componentName, $propsString, $uuid, $trace)
    {
        $serverBundle = file_get_contents($this->serverBundlePath);
        $this->phpExecJs->createContext($this->consolePolyfill()."\n".$serverBundle);
        $result = json_decode($this->phpExecJs->evalJs($this->wrap($componentName, $propsString, $uuid, $trace)), true);
        if ($result['hasErrors']) {
            $this->LogErrors($result['consoleReplayScript']);
            if ($this->failLoud) {
                $this->throwError($result['consoleReplayScript'], $componentName);
            }
        }
        return $result['html'].$result['consoleReplayScript'];
    }

    protected function consolePolyfill()
    {
        $console = <<<JS
var console = { history: [] };
['error', 'log', 'info', 'warn'].forEach(function (level) {
  console[level] = function () {
    var argArray = Array.prototype.slice.call(arguments);
    if (argArray.length > 0) {
      argArray[0] = '[SERVER] ' + argArray[0];
    }
    console.history.push({level: level, arguments: argArray});
  };
});
JS;
        return $console;
    }

    protected function wrap($name, $propsString, $uuid, $trace)
    {
        $traceStr = $trace ? 'true' : 'false';
        $wrapperJs = <<<JS
(function() {
  var props = $propsString;
  return ReactOnRails.serverRenderReactComponent({
    name: '$name',
    domNodeId: '$uuid',
    props: props,
    trace: $traceStr,
    location: ''
  });
})()
JS;
        return $wrapperJs;
    }

    protected function logErrors($consoleReplay)
    {
        $report = $this->extractErrorLines($consoleReplay);
        foreach ($report as $line) {
            $this->logger->warning($line);
        }
    }

    protected function extractErrorLines($consoleReplay) 
    {
        $report = [];
        $lines = explode("\n", $consoleReplay);
        $usefulLines = array_slice($lines, 2, count($lines) - 4);
        foreach ($usefulLines as $line) {
            if (preg_match ('/console\.error\.apply\(console, \["\[SERVER\] (?P<msg>.*)"\]\);/' , $line, $matches)) {
                $report[] = $matches['msg'];
            }
        }
        return $report;
    }
    
    protected function throwError($consoleReplay, $componentName)
    {
        $report = implode("\n", $this->extractErrorLines($consoleReplay));
        throw new EvalJsException($componentName, $report);
    }
}
