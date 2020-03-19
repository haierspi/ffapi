<?php
namespace ff\nosql;

use ff\base\Exception;


class RedisException extends Exception
{

    public function __construct(string $message, string $serkey)
    {

        $message = "{$serkey}: Redis " . $message;
        parent::__construct($message);
    }

}
