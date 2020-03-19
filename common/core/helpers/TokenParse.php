<?php
namespace ff\helpers;

use ff;

class TokenParse
{

    public static function get($token)
    {
        list($userid, $nickname, $expiration, $pwcode) = explode("\t", StringLib::myEncrypt($token, 'DE', ff::$config['encryptkey']));
        return [$userid, $nickname, $expiration, $pwcode];
    }

    public static function set($userid, $nickname, $expiration, $pwcode = null)
    {
        return StringLib::myEncrypt("$userid\t$nickname\t$expiration".($pwcode?("\t".$pwcode):''), 'EN', ff::$config['encryptkey']);
    }

}
