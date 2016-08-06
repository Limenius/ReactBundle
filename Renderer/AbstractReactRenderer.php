<?php

namespace Limenius\ReactBundle\Renderer;

use Psr\Log\LoggerInterface;
use Limenius\ReactBundle\Exception\EvalJsException;

abstract class AbstractReactRenderer
{
    abstract public function render($componentName, $propsString, $uuid, $registeredStores = array(), $trace);

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

    protected function initializeReduxStores($registeredStores = array())
    {
        if (!is_array($registeredStores) || empty($registeredStores)) {
            return "";
        }

        $result = '';
        foreach ($registeredStores as $storeName => $reduxProps) {
            $result .= <<<JS
reduxProps = $reduxProps;
storeGenerator = ReactOnRails.getStoreGenerator('$storeName');
store = storeGenerator(reduxProps);
ReactOnRails.setStore('$storeName', store);
JS;
        }

        return $result;
    }

    protected function wrap($name, $propsString, $uuid, $registeredStores = array(), $trace)
    {
        $traceStr = $trace ? 'true' : 'false';
        $initializedReduxStores = $this->initializeReduxStores($registeredStores);
        $wrapperJs = <<<JS
(function() {
  $initializedReduxStores
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
