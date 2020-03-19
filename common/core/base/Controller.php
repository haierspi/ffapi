<?php
namespace ff\base;

use ff;
use ff\network\Request;

abstract class Controller
{
    public $request;
    public $view;
    public $defaultAction = 'Index';
    public $runControllerClassName = '';
    public $actionAllowMethods;
    public $routerPath;
    public $authController;


    public function __construct(Request $request)
    {
        $this->request = $request;
    }
    public function beforeAction()
    {
    }

    public function afterAction()
    {
    }

    public function checkmethod($method)
    {
        $methods = explode('|', $method);
        if (in_array($this->request->method, $methods)) {
            return true;
        } else {
            return false;
        }

    }
    public function __call($name, $arguments) 
    {
        if ($this->authController) {
            if (method_exists( $this->authController, $name)) {
                return $this->authController->$name(...$arguments);
            }
        }
        return;
    }


}
