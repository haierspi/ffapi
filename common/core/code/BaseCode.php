<?php
namespace ff\code;

class BaseCode
{
    public function __construct()
    {
    }

    public static function __callStatic($callName, $callArray = [])
    {
        $codeBody = [];
        $codeBody['code'] = constant('static::' . $callName);
        if (defined('static::' . $callName . '_MSG') && defined('SYSTEM_CODE_MSG_DISPLAY') && constant('SYSTEM_CODE_MSG_DISPLAY') == 1) {
            $codeBody['msg'] = constant('static::' . $callName . '_MSG');
        }
        return array_merge($codeBody, (array) $callArray[0]);
    }

    public static function header(\ff\network\Response $Response)
    {
        //do  $Response->header();
    }

}
