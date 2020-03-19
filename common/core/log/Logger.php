<?php

namespace ff\log;

class Logger
{
    private $handler = null;
    private $module = '';
    public static $key = 'master';

    private static $instance = [];

    private function __construct()
    {}

    private function __clone()
    {}

    public static function Init($handler = null, $key = 'master')
    {
        self::$key = $key != '' ? $key : 'master';
        if (!self::$instance[$key] instanceof self) {
            self::$instance[$key] = new self();
            self::$instance[$key]->__setHandle($handler);

        }

        return self::$instance[$key];
    }

    private function __setHandle(LogHandler $handler)
    {
        $this->handler = $handler;
    }

    private function __setModule($module)
    {
        $this->module = $module;
    }

    public static function MOD($module)
    {

        self::$instance[self::$key]->__setModule($module);
        self::LOG('');

    }

    public static function LOG($msg, $title = '')
    {
        self::$instance[self::$key]->write($msg, $title);
    }
    public static function DEBUG($msg, $title = '')
    {
        self::$instance[self::$key]->write($msg, $title, 'DEBUG');
    }

    protected function write($msg, $title = '', $type = '')
    {

        $msgheader = '[' . date('Y-m-d H:i:s') . ']';
        if ($this->module) {
            $msgheader .= '[' . $this->module . ']';
        }

        if ($type) {
            $msgheader .= '[' . $type . ']';
        }
        if ($title) {
            $msgheader .= ' ' . $title;
        }

        if ($msg) {
            $logmsg = "\n" . $msg;
            $logmsg = str_replace("\n", "\n\t", $logmsg) . "\n";
        } else {
            $logmsg = "\n";
        }

        $this->handler->write($msgheader . $logmsg);
    }
}
