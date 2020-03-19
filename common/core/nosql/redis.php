<?php

namespace ff\nosql;

use ff\base\Component;

class redis extends Component
{
    public static $config = array();
    public static $link = array();
    public static $default = '';
    public static $curlink;
    private static $connected = false;

    public function __construct($config)
    {
        self::$default = $config['default'];
        self::$config = $config;
    }
    
    public static function __callStatic($name, $arguments)
    {
        if (!self::$connected) {
            self::connect();
        }

        if (!method_exists(self::$curlink, $name)) {
            throw new RedisException('Method Exists. [ ' . $name . ' ]', self::$default);
        }

        return call_user_func_array(array(self::$curlink, $name), $arguments);
    }

    public static function connect($confkey = '')
    {
        $confkey = $confkey ? $confkey : self::$default;
        if (empty(self::$link[$confkey])) {
            $config = self::$config[$confkey];
            if (!empty($config['server'])) {
                self::$default = $confkey;
                $prefix = $config['prefix'];
                self::$link[$confkey] = new \Redis();

                if (self::$config['pconnect']) {
                    $connect = @self::$link[$confkey]->pconnect($config['server'], $config['port'], $config['connect_timeout']);
                } else {
                    $connect = @self::$link[$confkey]->connect($config['server'], $config['port'], $config['connect_timeout']);
                }

                if (!$connect) {
                    throw new RedisException("Server Connection Error.", $confkey);
                }

                if ($connect) {
                    if ($config['password']) {
                        if (!self::$link[$confkey]->auth($config['password'])) {
                            throw new RedisException("Server NOAUTH Authentication required.", $confkey);
                        }
                    }
                    if ($config['prefix']) {
                        self::$link[$confkey]->setOption(\Redis::OPT_PREFIX, $config['prefix']);
                    }

                    if (!self::$link[$confkey]->select($config['db'])) {
                        throw new RedisException("Server Select Database Error.", $confkey);
                    }

                }
            }
        }
        self::$curlink = self::$link[$confkey];

        self::$connected = true;
        return self::$link[$confkey];
    }

}
