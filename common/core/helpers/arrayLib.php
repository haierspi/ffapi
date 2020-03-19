<?php
namespace ff\helpers;

class arrayLib
{

    public static function is_assoc($arr)
    {
        return array_keys($arr) === range(0, count($arr) - 1);
    }

}
