<?php

namespace ff\log;


class LogFileHandler implements LogHandler
{
	private $handle = null;
	
	public function __construct($file = '')
	{
		$this->handle = fopen($file,'a');
	}
	
	public function write($msg)
	{
		fwrite($this->handle, $msg);
	}
	
	public function __destruct()
	{
		fclose($this->handle);
	}
}