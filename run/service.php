<?php
chdir(__DIR__);

require '../vendor/autoload.php';
require '../common/core/FF.php';

class WebService
{

    private static $instance;

    private $_serv;
    private $config;

    public static function getInstance()
    {

        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct()
    {
    }

    public function start()
    {

        $server = new Swoole\Http\Server("0.0.0.0", 9501, SWOOLE_PROCESS);

        /**
         * 测试在 $server 外部注册全局自定义属性, 看看会不会被覆盖
         */

        $server->myWorkerVar = 'global';

        $server->set(array(
            'worker_num' => 1,
            'max_connection' => 10240,
            'daemonize' => false,
            //配置静态文件根目录
            'http_parse_post' => false,
        ));

        $config = require '../config/config.php';
        $Application = new ff\base\Application($config);

        $mimeTypes = require SYSTEM_ROOT_PATH . '/data/mimeTypes.php';

        // 每个 Worker 进程启动或重启时都会执行
        $server->on('WorkerStart', function (\Swoole\Http\Server $server, int $workerId) use ($Application) {
            if ($workerId >= $server->setting['worker_num']) {
                swoole_set_process_name("FFapi Tasker");
            } else {
                swoole_set_process_name("FFapi Worker");
            }
        });

        $server->on('Request', function (\Swoole\Http\Request $swRequest, \Swoole\Http\Response $swResponse) use ($Application, $mimeTypes) {
            if ($swRequest->server['path_info'] == '/favicon.ico' || $swRequest->server['request_uri'] == '/favicon.ico') {
                $swResponse->status(404);
                return $swResponse->end();
            }
            if (preg_match('/^\/static/is', $swRequest->server['path_info'])) {
                $sendfile = SYSTEM_ROOT_PATH . '/run' . $swRequest->server['path_info'];
                $ext = substr($sendfile, strrpos($sendfile, '.') + 1);
                if ($mimeTypes[$ext]) {
                    $swResponse->header('Content-Type', $mimeTypes[$ext]);
                }
                $swResponse->sendfile($sendfile);
            } else {
                $applicationResponse = $Application->service($swRequest, $swResponse);
                $swResponse->end($applicationResponse);
            }
        });

        $server->start();

    }

}

WebService::getInstance()->start()

?>
