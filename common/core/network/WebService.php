<?php

namespace ff\network;

class WebService
{

    private $_serv;
    private $config;


    
    public function __construct($config)
    {
        $this->config = $config;
    }


    public function start()
    {


        //$Router = ff::createObject('ff\network\Router', ['ff\network\Request', 'ff\network\Response']);


        $server = new \Swoole\Http\Server($this->config['host'], $this->config['port'], SWOOLE_PROCESS);

        $server->set($this->config['parameters']);

        
        $server->myWorkerVar = 'global';

        // 每个 Worker 进程启动或重启时都会执行
        $server->on('WorkerStart', function (\Swoole\Http\Server $server, int $workerId)  {

            $Request2 = new ff\network\Request;

           var_dump( $Request2   );

            
        });
        
        // 浏览器连接服务器后, 页面上的每个请求均会执行一次,
        // 每次打开链接页面默认都是接收两个请求, 一个是正常的数据请求, 一个 favicon.ico 的请求
        $server->on('Request', function (\Swoole\Http\Request $request, \Swoole\Http\Response $response) use ($server) {
            if ($request->server['request_uri'] == '/favicon.ico') {
                $response->end();
                return;
            }

            $msg = 'hello swoole !';
            $response->end($msg);
        });
        
        $server->start();
        



        // $server = new Swoole\Http\Server("0.0.0.0", 9504);
        // $server->myWorkerVar = 'global';

        // $this->_serv->on('request', function (\Swoole\Http\Request $request, \Swoole\Http\Response $response) {
            
        //   //  ff::$Router = ff::createObject('ff\network\Router', ['ff\network\Request', 'ff\network\Response']);



          

        //     //$routerResponse = $Router->runController();



        //     $response->end("<h1>Hello Swoole. #".rand(1000, 9999)."</h1>".$routerResponse);
        // });

        // $this->_serv->on('WorkerStart', function ($serv, $worker_id){

        //     if($worker_id >= $serv->setting['worker_num']) {
        //         swoole_set_process_name("ffapi service task worker");
        //     } else {
        //         swoole_set_process_name("ffapi service event worker");
        //     }
        // });
        // $this->_serv->start();


    }

}
