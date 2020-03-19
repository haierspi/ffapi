<?php

chdir(__DIR__);

$config = require '../config/config.php';

require '../vendor/autoload.php';
require '../common/core/FF.php';

$Application = new ff\base\Application($config);
$Application->run();