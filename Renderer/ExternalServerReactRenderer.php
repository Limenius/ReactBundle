<?php

namespace Limenius\ReactBundle\Renderer;

use Nacmartin\PhpExecJs\PhpExecJs;
use Psr\Log\LoggerInterface;
use Limenius\ReactBundle\Exception\EvalJsException;

class ExternalServerReactRenderer extends AbstractReactRenderer
{
    protected $logger;
    protected $serverSocketPath;
    protected $failLoud;

    public function __construct(LoggerInterface $logger, $serverSocketPath, $failLoud = false)
    {
        $this->logger = $logger;
        $this->serverSocketPath = $serverSocketPath;
        $this->failLoud = $failLoud;
    }

    public function setServerSocketPath($serverSocketPath)
    {
        $this->serverSocketPath = $serverSocketPath;
    }

    public function render($componentName, $propsString, $uuid, $registeredStores = array(), $trace)
    {
        $sock = stream_socket_client('unix://'.$this->serverSocketPath, $errno, $errstr);
        fwrite($sock, $this->wrap($componentName, $propsString, $uuid, $registeredStores, $trace));

        $contents = '';

        while (!feof($sock)) {
            $contents .= fread($sock, 8192);
        }
        fclose($sock);

        $result = json_decode($contents, true);
        if ($result['hasErrors']) {
            $this->LogErrors($result['consoleReplayScript']);
            if ($this->failLoud) {
                $this->throwError($result['consoleReplayScript'], $componentName);
            }
        }
        return $result['html'].$result['consoleReplayScript'];
    }
}
