<?php

namespace ff\log;

class network_logger
{
    private $handler = null;
    private $module = '';
    public static $key = 'master';

    public function __construct($handler = null)
    {
		$this->__setHandle($handler);
	}

    private function __clone()
    {}

    private function __setHandle(LogHandler $handler)
    {
        $this->handler = $handler;
    }

    public function log($data)
    {
		$datajson = \serialize($data) . "\n";
        $this->write($datajson);
    }

    protected function write($data)
    {
        $this->handler->write($data);
    }
}
