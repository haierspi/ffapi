<?php

namespace ff\helpers;

class lang
{

    public static $langs;
    public static function loaddata()
    {
        self::$langs = require SYSTEM_ROOT_PATH . '/data/lang.php';

    }

    public static function echo ($var) {

        if(!isset(self::$langs)){
            self::loaddata();
        }
        return isset(self::$langs[$var]) ? self::$langs[$var] : $var;
    }

}
