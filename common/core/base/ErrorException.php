<?php

namespace ff\base;

use ff\log\LogFileHandler;
use ff\log\Logger;

class ErrorException
{

    public static function Error($errno, $errstr, $errfile, $errline)
    {
        throw new \Error($errstr, $errno);
    }

    public static function Exception($ex)
    {

        if (defined('SYSTEM_DEBUG_ERRORLOG')) {

            $logHandler = new LogFileHandler(SYSTEM_ROOT_PATH . "/runtime/log/debug_errorexception.log");
            $Logger = Logger::Init($logHandler, 15);

            $Trace = $ex->getTrace();

            $logcont .= "Type:\n\t" . (($ex instanceof \Error) ? 'ff\base\ErrorException::Error' : 'ff\base\ErrorException::Exception') . "\n";
            $logcont .= "Message:\n\t" . $ex->getMessage() . "\n";
            $logcont .= "Request:\n\t" . json_encode(\ff::$network['request']->getVars()) . "\n";

            if ($Trace[0]['class'] == 'ff\base\ErrorException') {

                $logcont .= "File:\n\t" . $Trace[0]['args'][2] . " [ " . $Trace[0]['args'][3] . " ]\n";
                $logcont .= "Class:\n\t" . $Trace[1]['class'] . ' ' . $Trace[1]['type'] . ' ' . $Trace[1]['function'] . "\n";
                $logcont .= "Arguments:\n\t" . json_encode($Trace[1]['args']) . "\n";
                $logcont .= "Trace:\n" . str_replace("\n", "\n\t", "\t" . $ex->getTraceAsString());
            } else {
                $logcont .= "File:\n\t" . $ex->getFile() . " [ " . $ex->getLine() . " ]\n";
                $logcont .= "Class:\n\t" . $Trace[0]['class'] . ' ' . $Trace[0]['type'] . ' ' . $Trace[0]['function'] . "\n";
                $logcont .= "Arguments:\n\t" . json_encode($Trace[0]['args']) . "\n";
                $logcont .= "Trace:\n" . str_replace("\n", "\n\t", "\t" . $ex->getTraceAsString());
            }

            $Logger->DEBUG($logcont);
        }

        throw $ex;

    }
}
