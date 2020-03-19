<?php
namespace ff\network;

use ff;
use ff\base\Controller;
use ff\base\Exception;
use ReflectionMethod;

class Router
{

    const MODE_CGI = 0;
    const MODE_SWOOLE = 1;
    const MODE_CLI = 2;
    private $routerKeys = ['_VERSION', '_CONTROLLER', '_ACTION', '_FORMAT'];
    private $_PATHVERSION = '';
    private $envVars = [];
    public $actionMethod = '';
    public $request;
    public $response;

    public $urlManager;

    private $logger;

    public function __construct($requestClass, $responseClass, $mode = SELF::MODE_CGI)
    {

        $this->request = ff::createObject($requestClass, [$mode]);
        $this->response = ff::createObject($responseClass, [$mode]);

        $this->urlManager = ff::createObject(ff::$config['urlManager']['class'], [ff::$config['urlManager']['config']]);

    }

    public function init($swRequest = null, $swResponse = null)
    {

        $this->request->init($swRequest);
        $this->response->init($swResponse);
        $this->urlManager->init($this->request->requestPath);

        if ($this->_VERSION) {
            $this->_PATHVERSION = str_replace('.', '_', $this->_VERSION);
        }
    }

    public function response($outVars)
    {
        $response_output = $this->response->router_output($outVars, $this);

        if ($this->logger) {
            $logdata = [
                'method' => $this->request->method,
                'format' => $this->_FORMAT,
                'vars' => $this->request->vars,
                'headervars' => $this->request->headerVars,
                'actionmethod' => $this->actionMethod,
                'response' => $response_output,
                'timestamp' => TIMESTAMP,
                'clientip' => $this->request->clientip,
            ];

            $this->logger->log($logdata);

        }
        return $response_output;
    }

    public function __set($name, $value)
    {
        if (!in_array($name, $this->routerKeys)) {
            throw new Exception('Setting unknown property: ' . get_class($this) . '::' . $name, 0);
        }
        $this->envVars[$name] = $value;
    }

    public function __get($name)
    {
        if (isset($this->urlManager->$name)) {
            return $this->urlManager->$name;
        }
        if (!in_array($name, $this->routerKeys)) {
            throw new Exception('Getting unknown property: ' . get_class($this) . '::' . $name, 0);
        }
        return $this->envVars[$name] ?: null;
    }

    public function runController()
    {

        $runControllerName = "controllers\\" . ($this->_VERSION ? $this->_PATHVERSION . '\\' : '') . $this->_CONTROLLER . 'Controller';

        $routerPath = ($this->_VERSION ? $this->_PATHVERSION . '\\' : '') . $this->_CONTROLLER;

        $runControllerfile = constant('SYSTEM_CONTROLLERS_PATH') . "/" . ($this->_VERSION ? $this->_PATHVERSION . '/' : '') . $this->_CONTROLLER . 'Controller.php';

        if (!class_exists($runControllerName) && !file_exists($runControllerfile)) {
            return $this->response(['code' => -1001]);
        }

        $runController = ff::createObject($runControllerName, [$this->request]);
        $runController->runControllerClassName = $runControllerName;

        $runController->runAction = $runAction = 'action' . ($this->_ACTION ?: $runController->defaultAction);

        $routerPath .= '\\' . ($this->_ACTION ?: $runController->defaultAction);

        $runController->routerPath = $routerPath;

        $this->actionMethod = ($this->_VERSION ? $this->_VERSION . '/' : '') . $this->_CONTROLLER . '/' . ($this->_ACTION ?: $runController->defaultAction);

        if (isset(ff::$config['accesslogger']['enable']) && ff::$config['accesslogger']['enable'] && !preg_match('/' . join('|', ff::$config['accesslogger']['exclude']) . '/is', $this->actionMethod)) {

            $this->logger = ff::createObject(ff::$config['accesslogger']['class'], [new ff::$config['accesslogger']['handlerclass'](SYSTEM_ROOT_PATH . "/" . ff::$config['accesslogger']['savepath'] . md5($this->actionMethod) . ".log")]);
        }

        if (!method_exists($runController, $runAction) || !$runController instanceof Controller) {
            return $this->response(['code' => -1001]);
        }

        $actionReflection = new ReflectionMethod($runControllerName, $runAction);

        $ParamDefaultValue = $callFunctionParamValue = [];

        if ($actionReflection->getNumberOfParameters() > 0) {
            $actionReflectionParameters = $actionReflection->getParameters();

            $actionParamkeys = array();
            foreach ($actionReflectionParameters as $key => $reflectionParameter) {
                if (in_array($reflectionParameter->name, ['method', 'auth', 'rest'])) {
                    $actionParamkeys[$reflectionParameter->name] = $key;
                } else {
                    $constantName = basename($reflectionParameter->getDefaultValueConstantName());
                    if (preg_match('/^_PARAMS(_(\d))?$/s', basename($constantName), $match)) {
                        if (!defined($constantName)) {
                            define($constantName, basename($constantName));
                        }
                        if (isset($match[2])) {
                            $actionParamkeys[$reflectionParameter->name] = $_GET['_ACTION_PARAMS'][$match[2]];
                        } else {
                            $actionParamkeys[$reflectionParameter->name] = $_GET['_ACTION_PARAMS'];
                        }
                    }
                }
            }

            if (isset($actionParamkeys['auth'])) {
                $authkey = $actionParamkeys['auth'];

                if ($actionReflectionParameters[$authkey]->isDefaultValueAvailable()) {

                    $ParamDefaultValue['auth'] = $callFunctionParamValue[$authkey] = $actionReflectionParameters[$authkey]->getDefaultValue();

                    $authClassName = $actionReflectionParameters[$authkey]->getDefaultValue();

                    $authCallController = ff::createObject("ff\\auth\\" . $authClassName . 'AuthController', [$this->request]);
                    $authCallResult = $authCallController->beforeAction();
                    
                    $runController->authController = $authCallController;


                    
                    if (!is_null($authCallResult)) {
                        return $this->response($authCallResult);
                    }

                } else {
                    throw new Exception($runControllerName . '::' . $runAction . ': The $auth parameter must set the default value.', 0);
                }
            }

            if (isset($actionParamkeys['method'])) {
                $methodkey = $actionParamkeys['method'];
                if ($actionReflectionParameters[$methodkey]->isDefaultValueAvailable()) {

                    $ParamDefaultValue['method'] = $callFunctionParamValue[$methodkey] = explode('|', $actionReflectionParameters[$methodkey]->getDefaultValue());

                } else {
                    throw new Exception($runControllerName . '::' . $runAction . ': The $method parameter must set the default value.', 0);
                }
                $runController->actionAllowMethods = $ParamDefaultValue['method'];
            } else {
                $runController->actionAllowMethods = [
                    'GET', 'POST', 'PUT', 'DELETE', 'OPTIONS', 'PATCH',
                ];
            }
            $runController->runMethod = $this->request->method;

            if (isset($actionParamkeys['rest'])) {
                $restkey = $actionParamkeys['rest'];
                if ($actionReflectionParameters[$restkey]->isDefaultValueAvailable()) {

                    $restConf = $ParamDefaultValue['rest'] = $callFunctionParamValue[$restkey] = $actionReflectionParameters[$restkey]->getDefaultValue();

                    $restClassName = isset($restConf['class']) ? $restConf['class'] : 'ff\database\Rest';

                    $restClass = ff::createObject($restClassName);
                    $restClass->init($restConf['model'], is_array($restConf['config']) ?: [], (array) $ParamDefaultValue['method'], $this->request, $_GET['_ACTION_PARAMS']);
                    $restResult = $restClass->run();
                } else {
                    throw new Exception($runControllerName . '::' . $runAction . ': The $rest parameter must set the default value.', 0);
                }
            } elseif (isset($actionParamkeys['method'])) {
                if ($this->request->method == 'OPTIONS') {

                    $this->response->header('Access-Control-Allow-Credentials', 'true');
                    $this->response->header("Access-Control-Allow-Headers", 'Origin, X-Requested-With, Token, Content-Type, Accept, Authorization');
                    $this->response->header('Access-Control-Allow-Methods', join(',', $ParamDefaultValue['method']));

                    if (isset($this->request->vars['domain'])) {
                        if (preg_match('/^http|https:\/\//is', $this->request->vars['domain'])) {
                            $domain = $this->request->vars['domain'];
                        } else {
                            $domain = 'http://' . $this->request->vars['domain'];
                        }
                        $this->response->header('Access-Control-Allow-Origin', $domain);
                    } else {
                        $this->response->header('Access-Control-Allow-Origin', '*');
                    }

                    exit();
                } else if (!in_array($this->request->method, $ParamDefaultValue['method'])) {
                    return $this->response(['code' => -1002]); // Request Method Error
                } else {
                    $callFunctionParamValue[$methodkey] = $this->request->method;
                }
            }
        }else{
            if ($this->request->method == 'OPTIONS') {

                $allMethods = [
                    'GET', 'POST', 'PUT', 'DELETE', 'OPTIONS', 'PATCH',
                ];

                $this->response->header('Access-Control-Allow-Credentials', 'true');
                $this->response->header("Access-Control-Allow-Headers", 'Origin, X-Requested-With, Token, Content-Type, Accept, Authorization');
                $this->response->header('Access-Control-Allow-Methods', join(',', $allMethods));

                if (isset($this->request->vars['domain'])) {
                    if (preg_match('/^http|https:\/\//is', $this->request->vars['domain'])) {
                        $domain = $this->request->vars['domain'];
                    } else {
                        $domain = 'http://' . $this->request->vars['domain'];
                    }
                    $this->response->header('Access-Control-Allow-Origin', $domain);
                } else {
                    $this->response->header('Access-Control-Allow-Origin', '*');
                }

                exit();
            }

        }

        $classCallFunctions = array();
        $classCallFunctions[] = 'beforeAction';
        $classCallFunctions[] = $runAction;
        $classCallFunctions[] = 'afterAction';

        foreach ($classCallFunctions as $callfunction) {
            if ($runAction == $callfunction) {
                if (isset($callFunctionParamValue) && $callFunctionParamValue) {

                    $return = call_user_func_array([$runController, $callfunction], $callFunctionParamValue);
                } else {
                    $return = call_user_func([$runController, $callfunction]);
                }
            } else {
                $return = $runController->$callfunction();
            }
            if (null !== $return) {
                break;
            }
        }
        return $this->response($return);
    }
}
