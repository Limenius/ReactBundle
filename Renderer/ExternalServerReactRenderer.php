<?php

namespace Limenius\ReactBundle\Renderer;

use Psr\Log\LoggerInterface;

/**
 * Class ExternalServerReactRenderer
 */
class ExternalServerReactRenderer extends AbstractReactRenderer
{
    /**
     * @var string
     */
    protected $serverSocketPath;

    /**
     * @var bool
     */
    protected $failLoud;

    /**
     * ExternalServerReactRenderer constructor.
     *
     * @param LoggerInterface $logger
     * @param string          $serverSocketPath
     * @param bool            $failLoud
     */
    public function __construct(LoggerInterface $logger, $serverSocketPath, $failLoud = false)
    {
        $this->logger = $logger;
        $this->serverSocketPath = $serverSocketPath;
        $this->failLoud = $failLoud;
    }

    /**
     * @param string $serverSocketPath
     */
    public function setServerSocketPath($serverSocketPath)
    {
        $this->serverSocketPath = $serverSocketPath;
    }

    /**
     * @param string $componentName
     * @param string $propsString
     * @param string $uuid
     * @param array  $registeredStores
     * @param bool   $trace
     *
     * @return string
     */
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
            $this->logErrors($result['consoleReplayScript']);
            if ($this->failLoud) {
                $this->throwError($result['consoleReplayScript'], $componentName);
            }
        }

        return $result['html'].$result['consoleReplayScript'];
    }
}
