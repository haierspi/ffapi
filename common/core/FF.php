<?php

require __DIR__ . '/BaseFF.php';
require __DIR__ . '/ToolsFunc.php';

class ff extends ff\base {}

spl_autoload_register(['ff', 'autoload'], true, true);
ff::$classMap = require SYSTEM_CORE_PATH . '/classes.php';
ff::$container = new ff\di\Container();

//set_error_handler(['ff\base\ErrorException', 'Error'], E_ALL & ~E_NOTICE);
//set_exception_handler(['ff\base\ErrorException', 'Exception']);

