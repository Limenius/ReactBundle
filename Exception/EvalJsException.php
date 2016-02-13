<?php

namespace Limenius\ReactBundle\Exception;

class EvalJsException extends \RuntimeException
{
    public function __construct($componentName, $consoleReplay) {
        $message = 'Error rendering component '.$componentName."\nConsole log:".$consoleReplay;
        return parent::__construct($message);
    }
}
