<?php

namespace ff\log;


interface LogHandler
{
	public function write($msg);
	
}
