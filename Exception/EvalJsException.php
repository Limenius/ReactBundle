<?php

namespace Limenius\ReactBundle\Exception;

/**
 * Class EvalJsException
 */
class EvalJsException extends \RuntimeException
{
    /**
     * EvalJsException constructor.
     *
     * @param string $componentName
     * @param int    $consoleReplay
     */
    public function __construct($componentName, $consoleReplay)
    {
        $message = 'Error rendering component '.$componentName."\nConsole log:".$consoleReplay;
        parent::__construct($message);
    }
}
