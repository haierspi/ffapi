<?php
namespace ff\base;

use ff;
use ff\database\db;
use ff\network\Request;
use ff\network\WebService;
use ff\view\template;
use Illuminate\Database\Capsule\Manager;

class Application
{

    public function __construct($config)
    {
        ff::$config = $config;
        $this->initComponents();
    }

    private function initComponents()
    {

        if (!isset(ff::$config['components'])) {
            return;
        }

        ff::$app = new \stdClass;

        foreach (ff::$config['components'] as $appComKey => $appComConf) {
            $classname = $appComConf['class'];
            if (is_subclass_of($classname, 'ff\base\Componentif')) {
                unset($appComConf['class']);
                ff::$app->$appComKey = ff::createObject($classname, $appComConf);
            }
        }
    }

    /* 业务级 核心方法 */

    public function run()
    {

        ff::$app->router = ff::createObject('ff\network\Router', ['ff\network\Request', 'ff\network\Response', constant('SYSTEM_RUN_MODE') == 'cli' ? (ff\network\Router::MODE_CLI) : (ff\network\Router::MODE_CGI)]);
        ff::$app->router->init();
        ff::$app->router->runController();
        
    }

    // 以服务的方式启动
    public function service(\Swoole\Http\Request $swRequest, \Swoole\Http\Response $swResponse)
    {
        ff::$app->router = ff::createObject('ff\network\Router', ['ff\network\Request', 'ff\network\Response', ff\network\Router::MODE_SWOOLE]);

        ff::$app->router->init($swRequest, $swResponse);
        return ff::$app->router->runController();
    }

}
