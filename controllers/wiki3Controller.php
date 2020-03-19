<?php
namespace controllers;

use ff;
use ff\base\Controller;
use ff\helpers\wiki;
use models\usermodel;

class wikiController extends Controller
{
    public $skipDisplayControllers = ['wiki'];

    public function actionIndex()
    {

        $APIS = $controllers = array();

        $mpath = SYSTEM_CONTROLLERS_PATH;

        $mdir = dir($mpath);
        while (false !== ($dir = $mdir->read())) {

            if ($dir == '.' || $dir == '..') {
                continue;
            }

            if (preg_match('/([a-z0-9]+)Controller/is', $dir, $matchs)) {
                if (!in_array($matchs[1], $this->skipDisplayControllers)) {
                    $controllers[] = $matchs[1];
                }
            } elseif (preg_match('/v([a-z0-9\_]+)/is', $dir)) {
                $vermdir = dir($mpath . PATHSEPARATOR . $dir);
                while (false !== ($verdir = $vermdir->read())) {
                    if ($verdir == '.' || $verdir == '..') {
                        continue;
                    }

                    if (preg_match('/([a-z0-9]+)Controller/is', $verdir, $matchs)) {
                        $controllers[] = $dir . '/' . $matchs[1];
                    }
                }

            }

        }
        asort($controllers);

        $wiki = new wiki();

        foreach ($controllers as $controller) {
            $ControllerClass = $controller . 'Controller';
            $ControllerFile = SYSTEM_CONTROLLERS_PATH . PATHSEPARATOR . $ControllerClass . PHPEXT;

            list($APIS[$controller]['name'], $APIS[$controller]['description']) = $wiki->getControllerName($ControllerFile);

            $AllActions = $wiki->getAllActions($ControllerFile);

            $APINAME = str_replace('_', '.', $APINAME);


            foreach ($AllActions as $key => $one) {
                $key = strtolower($key);
                $one['action'] = strtolower($one['action']);
                $one['caction'] = str_replace('_', '.', $controller) . '/' . strtolower($one['action']);
                $APIS[$controller]['apis'][$key] = $one;
            }
        }

        viewAssign('baseurl','/');
        viewAssign('APIS',$APIS);


        return view('wiki/index');

    }

    public function actionDoc()
    {

        $display = $this->request->vars['display'];

        if (strpos($display, '/') === false) {
            header("HTTP/1.1 404 Not Found");
            header("Status: 404 Not Found");
            exit();
        }

        preg_match('/((v[0-9a-zA-Z\.\_]+)\/?)?(([0-9a-zA-Z_\-]+)\/?)([0-9a-zA-Z_\-]+)?/is', $display, $matches);
        list($APINAME, , $VERSION, , $CONTROLLER, $ACTION) = $matches;


        
       // $APINAME = str_replace('_', '.', $APINAME);


        $Tags = [
            'name' => '接口名称',
            'method' => '请求方式',
            'format' => '返回格式',
            'param' => '请求参数',
            'var' => '返回字段',
            'other' => '其他备注说明',
            'example' => '返回示例',
            'author' => '作者',
        ];

        $dataparam = [
            ['type' => 'string', 'method' => 'ALL', 'varname' => 'sign', 'must' => 'no', 'description' => '签名 ( <a href="wiki/sign" target="iframe" onclick="menuselect($(\'sign\')) ">签名计算方式 <img src="static/image/linktarget.png"></a> )'],
            ['type' => 'string', 'method' => 'ALL', 'varname' => 'systemname', 'must' => 'no', 'description' => '系统标识 (版本控制使用,ios 或 android)'],
            ['type' => 'string', 'method' => 'ALL', 'varname' => 'systemver', 'must' => 'no', 'description' => '系统版本号(版本控制使用, 类型说明: int 或 float 或者 类 1.0.2 之类 由数字和小数点组成的字符串)'],
            ['type' => 'string', 'method' => 'ALL', 'varname' => 'appname', 'must' => 'no', 'description' => 'APP名称(版本控制使用)'],
            ['type' => 'string', 'method' => 'ALL', 'varname' => 'appver', 'must' => 'no', 'description' => 'APP版本名(版本控制使用)'],
            ['type' => 'string', 'method' => 'ALL', 'varname' => 'appvercode', 'must' => 'no', 'description' => 'APP版本号(版本控制使用, 类型说明: int 或 float 或者 类 1.0.2 之类 由数字和小数点组成的字符串)'],
            ['type' => 'string', 'method' => 'ALL', 'varname' => 'domain', 'must' => 'no', 'description' => '当调用接口需要发送跨域header头时使用本参数'],
        ];

        $vars = require SYSTEM_ROOT_PATH . '/data/vars.php';

        $ControllerName = $CONTROLLER . 'Controller';
        $VERSION = str_replace('.', '_', $VERSION);
        $ControllerFile = SYSTEM_CONTROLLERS_PATH . PATHSEPARATOR . ($VERSION ? $VERSION . PATHSEPARATOR : '') . $ControllerName . PHPEXT;
        $ActionName = 'action' . $ACTION;

        if (!is_file($ControllerFile)) {
            header("HTTP/1.1 404 Not Found");
            header("Status: 404 Not Found");
            exit();
        }

        $wiki = new wiki();
        $data = $wiki->getActionWikiData($ControllerFile, $ActionName);

        
        viewAssign('baseurl','/');
        viewAssign('Tags',$Tags);
        viewAssign('dataparam',$dataparam);
        viewAssign('data',$data);
        viewAssign('vars',$vars);
        viewAssign('APINAME',$APINAME);
    
        return view('wiki/doc');


    }

    public function actionTool()
    {
        $display = $this->request->vars['display'];

        if (strpos($display, '/') === false) {
            header("HTTP/1.1 404 Not Found");
            header("Status: 404 Not Found");
            exit();
        }

        preg_match('/(v([0-9a-zA-Z\.]+)\/?)?(([0-9a-zA-Z_\-]+)\/?)([0-9a-zA-Z_\-]+)?/is', $display, $matches);
        list($APINAME, , $VERSION, , $CONTROLLER, $ACTION) = $matches;
        $APINAME = str_replace('_', '.', $APINAME);


        viewAssign('baseurl','/');
        viewAssign('display',$display);
        viewAssign('APINAME',$APINAME);
    
        return view('wiki/tool');


    }

    // 读取访问日志
    public function readlog($file, $num)
    {
        $fp = fopen($file, "r");
        $pos = -2;
        $eof = "";
        $head = false; //当总行数小于Num时，判断是否到第一行了
        $lines = array();
        while ($num > 0) {
            while ($eof != "\n") {
                if (fseek($fp, $pos, SEEK_END) == 0) { //fseek成功返回0，失败返回-1
                    $eof = fgetc($fp);
                    $pos--;
                } else { //当到达第一行，行首时，设置$pos失败
                    fseek($fp, 0, SEEK_SET);
                    $head = true; //到达文件头部，开关打开
                    break;
                }

            }

            $data = unserialize(str_replace("\n", '', fgets($fp)));
            $data['request'] = json_encode($data['vars']);
            $lines[] = $data;

            if ($head) {break;} //这一句，只能放上一句后，因为到文件头后，把第一行读取出来再跳出整个循环
            $eof = "";
            $num--;
        }
        fclose($fp);
        return $lines;
    }
    public function actionClearlog()
    {

        $display = $this->request->vars['display'];

        preg_match('/(v([0-9a-zA-Z\.]+)\/?)?(([0-9a-zA-Z_\-]+)\/?)([0-9a-zA-Z_\-]+)?/is', $display, $matches);

        list($APINAME, , $VERSION, , $CONTROLLER, $ACTION) = $matches;

        $actionMethod = ($VERSION ? 'v' . $VERSION . '/' : '') . $CONTROLLER . '/' . $ACTION;

        $logfile = SYSTEM_ROOT_PATH . "/" . ff::$config['accesslogger']['savepath'] . md5($actionMethod) . ".log";

        if (file_exists($logfile)) {
            unlink($logfile);
        }

        header('Location: /wiki/log?display=' . $actionMethod);
        exit();

    }
    public function actionLog()
    {

        $display = $this->request->vars['display'];

        preg_match('/(v([0-9a-zA-Z\.]+)\/?)?(([0-9a-zA-Z_\-]+)\/?)([0-9a-zA-Z_\-]+)?/is', $display, $matches);

        list($APINAME, , $VERSION, , $CONTROLLER, $ACTION) = $matches;

        $actionMethod = ($VERSION ? 'v' . $VERSION . '/' : '') . $CONTROLLER . '/' . $ACTION;

        if (strpos($display, '/') === false || !isset(ff::$config['accesslogger']['enable']) || !ff::$config['accesslogger']['enable']) {
            header("HTTP/1.1 404 Not Found");
            header("Status: 404 Not Found");
            exit();
        }
        $logdata = [];
        $logfile = SYSTEM_ROOT_PATH . "/" . ff::$config['accesslogger']['savepath'] . md5($actionMethod) . ".log";
        if (file_exists($logfile)) {
            $logdata = $this->readlog($logfile, 100);
        }
        

        viewAssign('baseurl','/');
        viewAssign('logdata',$logdata);
        viewAssign('actionMethod',$actionMethod);
        viewAssign('display',$display);
    
        return view('log/content');


    }

    public function actionSign()
    {

        viewAssign('baseurl','/');
    
        return view('wiki/sign');
    }

    public function actionVars()
    {

        $vars = require SYSTEM_ROOT_PATH . '/data/vars.php';

        $baseurl = '/';
        if (!defined('RESPONSE_INCLUDE')) {
            $display = $_GET['display'];
            if ($display) {
                foreach ($vars as $key => $value) {
                    if ($display != $key) {
                        unset($vars[$key]);
                    }
                }
            }
        }
        
        viewAssign('baseurl','/');
        viewAssign('vars',$vars);
    
        return view('wiki/vars');

    }

    public function actionError()
    {
        $display = $this->request->vars['display'];
        $errors = require SYSTEM_ROOT_PATH . '/data/error.php';

        $displayerrors = [];

        if ($display) {

            foreach ($errors['code'] as $code => $error) {
                $level = substr($code, 0, 2);
                $displayerrors[$code] = ['code' => $code, 'level' => $level, 'leveltitle' => $errors['level'][$level], 'error' => $error];

            }
        } else {
            foreach ($errors['code'] as $code => $error) {
                $level = substr($code, 0, 2);
                if ($errors['level'][$level]) {
                    $displayerrors[$level]['title'] = $errors['level'][$level];
                    $displayerrors[$level]['data'][$code] = $error;
                }
            }

        }

        viewAssign('baseurl','/');
        viewAssign('displayerrors',$displayerrors);
    
        return view('wiki/error');
    }

    public function actionErrorException()
    {
        $display = $this->request->vars['display'];

        $logfile = SYSTEM_ROOT_PATH . "/runtime/log/debug_errorexception.log";

        $logfilecont = '';
        if (file_exists($logfile)) {
            $logfilecont = file_get_contents($logfile);
        }

        viewAssign('baseurl','/');
        viewAssign('logfilecont',$logfilecont);
    
        return view('wiki/errorexception');
    }

    public function actionClearErrorException()
    {
        $logfile = SYSTEM_ROOT_PATH . "/runtime/log/debug_errorexception.log";

        if (file_exists($logfile)) {
            unlink($logfile);
        }

        header('Location: /wiki/errorexception');
        exit();

    }
    public function __call($function_name, $arguments)
    {


    }
}
