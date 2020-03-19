<?php

//根据服务器获取环境变量信息

/*

CLI:  vi /etc/profile >> ADD export RUNTIME_ENVIROMENT=DEVELOPMENT  >> source /etc/profile

CGI:  phpfpm >> vim /etc/php-fpm.d/www.conf >> ADD env[RUNTIME_ENVIROMENT] = 'DEVELOPMENT' >> service php-fpm restart

 */

//项目名称
define('APP_NAME', 'OMSAPI');

define('RUNTIME_ENVIROMENT', getenv('RUNTIME_ENVIROMENT') ? getenv('RUNTIME_ENVIROMENT') : (isset($_SERVER['RUNTIME_ENVIROMENT']) ? $_SERVER['RUNTIME_ENVIROMENT'] : null));

// define('RUNTIME_ENVIROMENT', 'DEVELOPMENT'); // 开发环境
// //define('RUNTIME_ENVIROMENT','TESTING');     //测试环境
// //define('RUNTIME_ENVIROMENT','PRODUCTION');  // 生产环境
//开启错误打印
define('SYSTEM_DEBUG', E_ALL & ~E_NOTICE);
//开启系统错误日志收集; 日志路径 runtime/log/debug_errorexception.log
define('SYSTEM_DEBUG_ERRORLOG', 1);
//开启SQL DEBUG
define('SYSTEM_DEBUG_SQLLOG', 0);

$config = [

    //环境访问域名
    'ENV_HOST' => 'complaint.lwhs.me',
    'ENV_PROTOCOL' => 'http',

    //启用服务设置
    'service' => [
        //服务器IP
        'host' => '0.0.0.0',
        //服务器端口
        'port' => '9501',
        'everyreload' => true,
        'parameters' => [
            'daemonize' => false,
            //'task_worker_num' => 2,
            'max_connection' => 1000,
            'max_request' => 10000,
            'worker_num' => 1,
            'enable_reuse_port' => true,
            'open_tcp_nodelay' => true,
            'log_file' => '/log/server.log',
            'pid_file' => '/server.pid',
            'heartbeat_check_interval' => 15,
            'heartbeat_idle_time' => 30,
        ],
    ],
    'kuaidi100'=>[
        'key'       => '',
        'customer'  => '',
        'secret'    => '',
        'salt'      => '',//自己定义回调salt
    ],
    //亚马逊
    'aws' => [
        'access_key_id' => '',
        'secret_access_key' => '',
        's3' => [
            'bucket' => '',
            'region' => '',
        ],
    ],

    'signkey' => 'FFAPI_DEV_AUTHENTICATION',
    'encryptkey' => 'FFAPI_DEV_AUTHENTICATION',
    //开启token

    //日志
    'accesslogger' => [
        'enable' => 1,
        'class' => 'ff\log\network_logger',
        'handlerclass' => 'ff\log\LogFileHandler',
        'savepath' => 'runtime/log/',
        'exclude' => [
            '^wiki',
        ],
    ],

    //服务器格式化工具
    'response' => [
        'json' => 'ff\helpers\JsonParser',
        'jsonp' => 'ff\response\jsonp',
        'xml' => 'ff\helpers\xmlParser',
    ],
    //开启wiki
    'wiki' => [
        'enable' => true,
        'port' => '8080',
        'path' => 'wiki',
    ],
    //cookie 设置
    'cookie' => [
        'cookiepre' => 'wSvr_',
        'cookiedomain' => '',
        'cookiepath' => '/',
    ],
    'urlManager' => [
        'class' => 'ff\network\urlManager',
        'config' => [
        ],
    ],
    'components' => [
        'sqlsrv' => [
            'class' => 'ff\database\sqlsrvConnection',
            'config' => [
                'default' => 'master',
                'master' => [
                    'host' => 'localhost',
                    'port' => '7698',
                    'username' => 'sa',
                    'password' => '',
                    'database' => '',
                    'prefix' => '',
                ],
                'online' => [
                    'host' => 'localhost',
                    'port' => '7698',
                    'username' => 'sa',
                    'password' => '',
                    'database' => '',
                    'prefix' => '',
                ],
            ],
        ],

        'redis' => [
            'class' => 'ff\nosql\redis',
            'config' => [
                'default' => 'master',
                'master' => [
                    'server' => '127.0.0.1',
                    'port' => 6379,
                    'pconnect' => 1,
                    'connect_timeout' => 0,
                    'password' => '',
                    'db' => 0,
                    'prefix' => '',
                ],
            ],
        ],
        'db' => [
            'class' => 'ff\database\Connection',
            'config' => [
                'default' => 'master',

                'master' => [
                    'dsn' => 'mysql:host=localhost;dbname=branches;charset=UTF8;',
                    'username' => '',
                    'password' => '',
                    'tablepre' => '',
                    'options' => [],
                ],

                'fbadauto' => [
                    'dsn' => 'mysql:host=localhost;dbname=dev_fbadauto;charset=utf8mb4;',
                    'username' => '',
                    'password' => '',
                    'tablepre' => '',
                    'options' => [],
                ],

            ],
        ],
    ],
];

//生产环境
if (constant('RUNTIME_ENVIROMENT') == 'PRODUCTION') {
    $config['components']['db'] = [
        'class' => 'ff\database\Connection',
        'config' => [
            'default' => 'master',
            'master' => [
                'dsn' => 'mysql:host=localhost;dbname=product_center_online;charset=UTF8;',
                'username' => '',
                'password' => '',
                'tablepre' => 'ly_',
                'options' => [],
            ],
            'fbadauto' => [
                'dsn' => 'mysql:host=localhost;dbname=fbadauto;charset=utf8mb4;',
                'username' => '',
                'password' => '',
                'tablepre' => 'ly_',
                'options' => [],
            ],
        ],
    ];
    $config['components']['sqlsrv'] = [
        'class' => 'ff\database\sqlsrvConnection',
        'config' => [
            'default' => 'master',
            'master' => [
                'host' => 'localhost',
                'port' => '7698',
                'username' => 'sa',
                'password' => '',
                'database' => '',
                'prefix' => '',
            ],
        ],
    ];
    $config['components']['redis'] = [
        'class' => 'ff\nosql\redis',
        'config' => [
            'default' => 'master',
            'master' => [
                'server' => 'localhost',
                'port' => 6379,
                'pconnect' => 1,
                'connect_timeout' => 0,
                'password' => '',
                'db' => 0,
                'prefix' => '',
            ],
        ],
    ];
}

//开发测试服务器配置信息
if (constant('RUNTIME_ENVIROMENT') == 'TESTING') {
    $config['components']['db'] = [
        'class' => 'ff\database\Connection',
        'config' => [
            'default' => 'master',
            'master' => [
                'dsn' => 'mysql:host=localhost;dbname=branches;charset=UTF8;',
                'username' => '',
                'password' => '',
                'tablepre' => 'ly_',
                'options' => [],
            ],
            'fbadauto' => [
                'dsn' => 'mysql:host=localhost;dbname=fbadauto;charset=utf8mb4;',
                'username' => '',
                'password' => '',
                'tablepre' => 'ly_',
                'options' => [],
            ],
        ],
    ];

    $config['components']['redis'] = [
        'class' => 'ff\nosql\redis',
        'config' => [
            'default' => 'master',
            'master' => [
                'server' => 'localhost',
                'port' => 6379,
                'pconnect' => 1,
                'connect_timeout' => 0,
                'password' => '',
                'db' => 1,
                'prefix' => '',
            ],
        ],
    ];
}


return $config;
