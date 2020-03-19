<?php
namespace ff\database;

use ff\base\Exception;

class DbException extends Exception
{
    public function __construct(string $message, int $code = 0, string $sql = '')
    {
        $sql = preg_replace("/\v/", '', $sql);
        $sql = preg_replace("/\s{2,}/is", ' ', $sql);
        $message = "SQL: {$sql}\r\n\t" . $message;
        parent::__construct($message, $code);
    }
}
