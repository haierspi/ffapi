<?php
namespace ff\base;

use ff\log\LogFileHandler;
use ff\log\Logger;

class Exception extends \Exception
{
    public function __construct($message, $code = 0)
    {
        parent::__construct($message);
    }
}
