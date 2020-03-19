<?php
namespace ff\base;

use ff\log\LogFileHandler;
use ff\log\Logger;
use phpDocumentor\Reflection\Types\This;

class Error extends \ErrorException
{
    public function __construct($message, $code, $severity, $filename, $lineno)
    {

        if (defined('SYSTEM_DEBUG_ERRORLOG')) {
            $logHandler = new LogFileHandler(SYSTEM_ROOT_PATH . "/runtime/log/debug_errorexception.log");

            $Logger = Logger::Init($logHandler, 15);

            $Trace = $this->getTrace();

            $logcont .= "Type:\n\tff\base\Error\n";
            $logcont .= "Message:\n\t" . $message . "\n";
            $logcont .= "Request:\n\t" . json_encode(\ff::$network['request']->getVars()) . "\n";
            $logcont .= "File:\n\t" . $this->getFile() . " [ " . $this->getLine() . " ]\n";
            $logcont .= "Class:\n\t" . $Trace[0]['class'] . ' ' . $Trace[0]['type'] . ' ' . $Trace[0]['function'] . "\n";
            $logcont .= "Arguments:\n\t" . json_encode($Trace[0]['args']) . "\n";
            $logcont .= "Trace:\n" . str_replace("\n", "\n\t", "\t" . $this->getTraceAsString());
            $Logger->DEBUG($logcont);
        }
        parent::__construct($message, $code, $severity, $filename, $lineno);
    }
}
