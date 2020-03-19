<?php

namespace ff\helpers;

class wikiParser
{

    private $__cRFClass;
    private $__cRFClasses = [];
    private $__cRFClassFunc;
    private $__cRFClassFunces = [];

    public $skipDisplayControllers = ['wiki'];
    public $controllers = [];
    public $controllerClasses = [];
    public $titleKeyList = [];

    public function __construct()
    {
    }

    public function parseMd($realpath, $path = '')
    {
        $parseResult = [];
        $docDir = dir($realpath);
        while (false !== ($docFile = $docDir->read())) {
            if ($docFile == '.' || $docFile == '..' || strpos($docFile, '.') === 0) {
                continue;
            }
            $index = $path == '' ? 1 : 1;

            $isFile = !is_dir($realpath . $docFile);
            $diSuffix = $isFile ? '' : '/';
            $realFilePath = $realpath . $docFile . $diSuffix;
            $relativeFilePath = $path . $docFile . $diSuffix;

            $title = $this->getPathTitle($realFilePath, $isFile);
            $index = str_replace('.md', '', $relativeFilePath);

            if ($isFile) {
                $this->titleKeyList[strtolower($index)] = $title;
            }

            $parseResult[$index] = [
                'realpath' => $realFilePath,
                'path' => $relativeFilePath,
                'isFile' => $isFile,
                'title' => $title,
                'list' => $isFile ? [] : $this->parseMd($realFilePath, $relativeFilePath),
            ];

        }
        $docDir->close();
        return $parseResult;
    }

    public function getPathTitle($realpath, $isFile = null)
    {
        if (is_null($isFile)) {
            $isFile = !is_dir($realpath);
        }
        return $isFile ? $this->getMdTitle($realpath) : $this->getDirTitle($realpath);
    }

    public function getSortList($listData)
    {
        $returnData = $dirsData = [];

        $returnData['index'] = $listData['index'];
        unset($listData['index']);

        foreach($listData as $key=>$oneData){
            if($oneData['isFile']){
                $returnData[$key] = $oneData;
            }else{
                $dirsData[$key] = $oneData;
            }

        }
        return array_merge($returnData,$dirsData);
    }

    //获取目录标题
    public function getDirTitle($realpath)
    {
        $title = '';
        $handle = @fopen($realpath . '.dirname', "r");
        if ($handle) {
            $content = fgets($handle, 4096);
            fclose($handle);
            $title = trim($content);
        }
        return $title;
    }


    //获取MD标题
    public function getMdTitle($realpath)
    {
        $title = '';
        $handle = @fopen($realpath, "r");
        if ($handle) {
            $content = fgets($handle, 4096);
            fclose($handle);
            $title = trim(substr(trim($content), 1));
        }
        return $title;
    }

    //获取MD内容
    public function getMdContent($realpath)
    {

        $title = $content = '';
        $handle = @fopen($realpath, "r");
        if ($handle) {
            $title = fgets($handle, 4096);
            fclose($handle);
            $content = substr(file_get_contents( $realpath ) ,strlen($title) );
        }
        return $content;
    }

    //解析Markdown 标题
    public function parseMdTitle($parseResult)
    {
        foreach ($parseResult as $key => $oneResult) {
            $title = '';
            if ($oneResult['isFile']) {

                $handle = @fopen($oneResult['realpath'], "r");
                if ($handle) {
                    $content = fgets($handle, 4096);
                    fclose($handle);
                    $title = trim(substr(trim($content), 1));
                }
            } else {
                $handle = @fopen($oneResult['realpath'] . '.dirname', "r");
                if ($handle) {
                    $content = fgets($handle, 4096);
                    fclose($handle);
                    $title = trim($content);
                }
            }
            $parseResult[$key]['title'] = $title;
        }
        return $parseResult;
    }

    /* 获取全部控制器 */
    public function getAllControllers()
    {

        $this->controllers = $this->controllerClasses = [];
        $mdir = dir(SYSTEM_CONTROLLERS_PATH);
        while (false !== ($dir = $mdir->read())) {
            if ($dir == '.' || $dir == '..') {
                continue;
            }

            if (preg_match('/(([a-z0-9]+)Controller)/is', $dir, $matchs)) {
                if (!in_array($matchs[2], $this->skipDisplayControllers)) {
                    $this->controllers[] = $matchs[2];
                    $this->controllerClasses[] = 'controllers\\' . $matchs[1];
                }
            } elseif (preg_match('/v([a-z0-9\_]+)/is', $dir)) {
                $vermdir = dir(SYSTEM_CONTROLLERS_PATH . PATHSEPARATOR . $dir);
                while (false !== ($verdir = $vermdir->read())) {
                    if ($verdir == '.' || $verdir == '..') {
                        continue;
                    }
                    if (preg_match('/(([a-z0-9]+)Controller)/is', $verdir, $matchs)) {
                        if (!in_array($matchs[2], $this->skipDisplayControllers)) {
                            $this->controllers[] = $dir . '/' . $matchs[2];
                            $this->controllerClasses[] = 'controllers\\' . $dir . '\\' . $matchs[1];
                        }

                    }
                }
            }
        }
        return [$this->controllers, $this->controllerClasses];
    }
    public function getAllActions($cRFObject = null)
    {

        $cRFObject = is_null($cRFObject) ? $this->cRFClass : $cRFObject;
        $allMethods = $cRFObject->getMethods(\ReflectionMethod::IS_PUBLIC);

        if (is_array($allMethods)) {
            foreach ($allMethods as $key => $methodRF) {
                if (strpos($methodRF->name, 'action') !== 0) {
                    unset($allMethods[$key]);
                }
            }
        }
        return $allMethods;

    }
    /* 获取控制器名称 */
    public function getControllerName($class = null)
    {
        if (!is_null($class)) {
            $this->getControllerRFClass($class);
        }

        $content = $this->getControllerDoc($class);
        $content = $this->handleDoc($content);
        $name = preg_replace('/\h*@name\h*/is', '', $content);
        return $name;
    }

    public function handleDoc($code)
    {
        preg_match('/^\/\*(.*)\*\/$/is', $code, $match);
        $content = $match[1];
        $content = trim(preg_replace('/\*?\h*\r\n\h*\*\h*/is', "\r\n", $content));
        return $content;
    }

    //获取控制器反射类
    public function getControllerRFClass($className)
    {
        if (!isset($this->cRFClasses[$className])) {
            $this->cRFClasses[$className] = new \ReflectionClass($className);
        }

        return $this->setCurrentControllerRFClass($this->cRFClasses[$className]);
    }

    //设置当前处理控制器RF
    public function setCurrentControllerRFClass($cRFClass)
    {
        $this->cRFClass = $cRFClass;
        return $this;
    }
    //获取控制器注释
    public function getControllerDoc($className = null)
    {
        $cRFObject = is_null($cRFObject) ? $this->cRFClass : $this->getControllerRFClass($className);

        return $cRFObject->getDocComment();
    }

    /* ------------------ FUNC ----------------- */

    //获取控制器入口反射类
    public function getControllerRFClassFunc($class, $name = null)
    {
        if (is_object($class)) {
            $classname = get_class($class);
        } else {
            $classname = $class;
        }

        if (!isset($this->cRFClassFunces[$className])) {
            $this->cRFClassFunces[$className . ':' . $name] = new \ReflectionMethod($class, $name);
        }

        return $this->setCurrentControllerRFClassFunc($this->cRFClassFunces[$className . ':' . $name]);
    }

    //设置当前处理控制器入口反射类
    public function setCurrentControllerRFClassFunc($cRFClassFunc)
    {
        $this->cRFClassFunc = $cRFClassFunc;
        return $this;
    }

    /* 获取控制器名称 */
    public function getActionName()
    {
        $content = $this->getActionDoc();
        $content = $this->parseActionDoc($content);
        $name = preg_replace('/\h*@name\h*/is', '', $content);
        return $name;
    }

    //获取入口注释
    public function getActionDoc($cRFClassFunc = null)
    {
        $cRFClassFunc = is_null($RFMethod) ? $this->cRFClassFunc : $cRFClassFunc;

        return $cRFClassFunc->getDocComment();
    }

    //解析入口注释内容
    public function parseActionDoc()
    {

        $content = $this->getActionDoc();
        $content = $this->handleDoc($content);

        $data = array(
            'name' => array(),
            'method' => array(),
            'auth' => array(),
            'rest' => array(),
            'format' => array(),
            'param' => array(),
            'var' => array(),
            'example' => array(),
            'author' => array(),
        );
        $dataName = [
            'name' => '接口名称',
            'method' => '请求方式',
            'auth' => '认证方式',
            'rest' => 'REST标准',
            'format' => '返回格式',
            'param' => '请求参数',
            'var' => '返回字段',
            'other' => '其他备注说明',
            'example' => '返回示例',
            'author' => '作者',
        ];

        $match = explode("\n", str_replace("\r", '', $content));

        $key = null;
        foreach ($match as $value) {
            if (!$value) {
                continue;
            }
            if (preg_match('/^@([a-zA-Z]+)\s*(.*)/is', $value) || !is_null($key)) {
                $m = preg_match('/^@([a-zA-Z]+)\s*(.*)/is', $value, $match2);

                if ($m) {
                    $key = $match2[1];
                    $val = $match2[2];
                } else {
                    $val = $value;
                }
            } else {
                $key = 'name';
                $val = $value;
            }

            $data[$key][] = $val;
        }

        foreach ($data as $k => $v) {
            if (is_array($v) && empty($v)) {
                $data[$k][] = '';
            }

        }

        $actionReflectionParameters = $this->cRFClassFunc->getParameters();

        foreach ($actionReflectionParameters as $key => $reflectionParameter) {
            if (in_array($reflectionParameter->name, ['method', 'auth', 'rest'])) {
                $parameterData[$reflectionParameter->name] = $reflectionParameter->getDefaultValue();
            }
        }

        echo '<pre>';
        var_dump($parameterData);
        echo '</pre>';
        exit;

        foreach ($data as $key => $value) {
            foreach ($value as $key2 => $value2) {
                $vars = array();
                $vars['name'] = $dataName[$key];
                if ($key == 'name') {
                    preg_match('/(\S+)\s*(.*)/is', $value2, $match2);

                    $vars['value'] = $match2[1];
                    $vars['description'] = $match2[2];
                    $value = $vars;
                } elseif ($key == 'param') {
                    preg_match('/([_a-zA-Z0-9\[,\]]+)\s*([_a-zA-Z0-9\[,\]]+)\s*([_a-zA-Z0-9\[,\]]+)\s*(.*)/is', $value2, $match2);
                    preg_match('/(\[([\w,]+)\])?([_a-zA-Z0-9\[,\]]+)/is', $match2[2], $match3);

                    $vars['type'] = $match2[1];
                    $vars['varname'] = $match3[3];
                    $vars['method'] = strtoupper($match3[2]) ?: 'ALL';
                    $vars['must'] = $match2[3];
                    $vars['description'] = $match2[4];

                    $value[$key2] = $vars;
                } elseif ($key == 'var') {
                    preg_match('/([_a-zA-Z0-9\[,\]]+)\s*([_a-zA-Z0-9\[,\]]+)\s*(.*)/is', $value2, $match2);
                    preg_match('/(\[([\w,]+)\])?([_a-zA-Z0-9\[,\]]+)/is', $match2[2], $match3);

                    $vars['type'] = $match2[1];
                    $vars['varname'] = $match3[3];
                    $vars['method'] = strtoupper($match3[2]) ?: 'ALL';
                    $vars['description'] = $match2[3];

                    $value[$key2] = $vars;
                } elseif ($key == 'example') {
                    preg_match('/((\[[\w\x{4e00}-\x{9fa5}]+\])+)\s*(([a-zA-Z\*]+):)?\s*(.*)/uis', $value2, $match2);

                    $vars['title'] = $match2[1];
                    $vars['format'] = strtoupper($match2[4]);
                    if ($vars['format'] == 'JSON') {
                        $content = json_decode($match2[5], true);
                        $vars['content'] = json_encode($content, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                    } else {
                        $vars['content'] = $match2[5];
                    }

                    $value[$key2] = $vars;

                } elseif (isset($parameterData[$key])) {
                    $vars['value'] = $parameterData[$key];
                    $value = $vars;

                } else {
                    $vars['value'] = $value2;
                    $value = $vars;
                }

            }
            $data[$key] = $value;

        }

        echo '<pre>';
        var_dump($data);
        echo '</pre>';
        exit;

        $name = preg_replace('/\h*@name\h*/is', '', $content);
        return $name;
    }

    public function actionWikiParse($action)
    {

        preg_match('/class(.*)\/\*/Uis', $this->content, $match, PREG_OFFSET_CAPTURE);
        $this->content = substr($this->content, strlen($match[1][0]) + $match[1][1]);
        preg_match('/.*(})/is', $this->content, $match, PREG_OFFSET_CAPTURE);
        $this->content = substr($this->content, 0, $match[1][1]);

        preg_match_all('/\s*\/\*(.*)\*\/\s*\h*public\h*function\h*(action[a-zA-Z0-9\-\_]+)\h*\(/iUs', $this->content, $matchs);

        $AllActionsContent = [];
        foreach ($matchs[2] as $key => $vaule) {
            $vaule = strtolower($vaule);
            $AllActionsContent[$vaule] = $matchs[1][$key];
        }

        $content = $AllActionsContent[$action];

        $content = preg_replace('/\h*\r\n\h*/is', "\n", $content);
        $match = preg_replace('/\n\s+\*/is', "\n*", $content);

        $data = array(
            'name' => array(),
            'method' => array(),
            'format' => array(),
            'param' => array(),
            'var' => array(),
            'example' => array(),
            'author' => array(),
        );

        foreach ($match as $value) {
            if (!$value) {
                continue;
            }
            $m = preg_match('/^@([a-zA-Z]+)\s*(.*)/is', trim($value), $match2);
            if ($m) {
                $key = $match2[1];
                $val = $match2[2];
            } else {
                $val = $value;
            }
            $data[$key][] = $val;
        }

        foreach ($data as $key => $value) {
            foreach ($value as $key2 => $value2) {
                if (!$value2) {
                    unset($value[$key2]);
                    continue;
                }
                if ($key == 'name') {
                    preg_match('/(\S+)\s*(.*)/is', $value2, $match2);

                    $vars = array();
                    $vars['title'] = $match2[1];
                    $vars['description'] = $match2[2];
                    $value = $vars;
                }
                if ($key == 'param') {
                    preg_match('/([_a-zA-Z0-9\[,\]]+)\s*([_a-zA-Z0-9\[,\]]+)\s*([_a-zA-Z0-9\[,\]]+)\s*(.*)/is', $value2, $match2);
                    preg_match('/(\[([\w,]+)\])?([_a-zA-Z0-9\[,\]]+)/is', $match2[2], $match3);

                    $vars = array();
                    $vars['type'] = $match2[1];
                    $vars['varname'] = $match3[3];
                    $vars['method'] = strtoupper($match3[2]) ?: 'ALL';
                    $vars['must'] = $match2[3];
                    $vars['description'] = $match2[4];

                    $value[$key2] = $vars;
                }
                if ($key == 'var') {
                    preg_match('/([_a-zA-Z0-9\[,\]]+)\s*([_a-zA-Z0-9\[,\]]+)\s*(.*)/is', $value2, $match2);
                    preg_match('/(\[([\w,]+)\])?([_a-zA-Z0-9\[,\]]+)/is', $match2[2], $match3);

                    $vars = array();
                    $vars['type'] = $match2[1];
                    $vars['varname'] = $match3[3];
                    $vars['method'] = strtoupper($match3[2]) ?: 'ALL';
                    $vars['description'] = $match2[3];

                    $value[$key2] = $vars;
                }
                if ($key == 'example') {
                    preg_match('/((\[[\w\x{4e00}-\x{9fa5}]+\])+)\s*(([a-zA-Z\*]+):)?\s*(.*)/uis', $value2, $match2);

                    $vars = array();
                    $vars['title'] = $match2[1];
                    $vars['format'] = strtoupper($match2[4]);
                    if ($vars['format'] == 'JSON') {
                        $content = json_decode($match2[5], true);
                        $vars['content'] = json_encode($content, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                    } else {
                        $vars['content'] = $match2[5];
                    }

                    $value[$key2] = $vars;

                }

            }
            if (!in_array($key, array('name', 'param', 'var', 'example'))) {
                $data[$key] = join('', $value);
            } else {
                $data[$key] = $value;
            }
        }

        return $data;

    }
}

/*
$wiki = new wiki();
$wiki->getcomment('./AppController.php','Index');

echo '<pre>';
var_dump( $wiki->content );
echo '</pre>';
exit;

 */
