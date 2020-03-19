<?php

namespace ff\helpers;

class bankdata
{

    public static $bankdata;
    public static function loaddata()
    {
        self::$bankdata = require SYSTEM_ROOT_PATH . '/data/bankdata.php';

    }

    public static function echo ($var) {

        if(!isset(self::$bankdata)){
            self::loaddata();
        }
        return isset(self::$bankdata[$var]) ? (self::$bankdata[$var]) : $var;
    }

}
