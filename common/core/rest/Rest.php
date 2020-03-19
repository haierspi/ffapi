<?php
namespace ff\rest;

use ff;
use ff\network\Request;

class Rest
{

    private $model;
    private $request;
    private $params;
    private $config;
    private $allowMethod;
    private $method;
    private $action;
    private $verbs;

    public function __construct()
    {
    }

    // 405 Method Not Allowed

    public function init(String $modelName, array $config = [], array $allowMethod = [], Request $request, array $params = [])
    {
        if (class_exists($modelName)) {
            $this->model = ff::createObject($modelName);
        } else {
            $this->model = ff::createObject('ff\database\Model');
            $this->model->quickTableName($modelName);
        }
        $this->request = $request;
        $this->params = $params;
        $this->config = $config;
        $this->allowMethod = $allowMethod;
        $this->method = $this->request->method;
        $this->verbs = $this->verbs();


    }

    public function run()
    {
        $actions = [];
        foreach ($this->verbs as $action => $methods) {
            if(in_array($this->method,$methods)){
                $actions[] = $action;
            }
        }
        if(count($actions) == 2){
            if($this->params){
                $this->action = $actions[1];
            }else{
                $this->action = $actions[0];
            }
        }elseif(count($actions) == 1){
            $this->action = current($actions);
        }else{
            return ['code'=>'405'];
        }
        return call_user_func([$this,$this->action]);
    }

    // 按页列出资源；
    public function index()
    {
        if($this->method == 'GET'){


        }elseif($this->method == 'HEAD'){
        }

    }

    //返回指定资源的详情；
    public function view()
    {
        if($this->method == 'GET'){


        }elseif($this->method == 'HEAD'){
        }
    }

    //创建新的资源；
    public function create()
    {
        
    }

    //更新一个存在的资源；
    public function update()
    {
    }  
    //删除指定的资源；
    public function delete()
    {
    }
    //返回支持的 HTTP 方法。
    public function options()
    {
    }

    protected function verbs()
    {
        return [
            'index' => ['GET', 'HEAD'],
            'view' => ['GET', 'HEAD'],
            'create' => ['POST'],
            'update' => ['PUT', 'PATCH'],
            'delete' => ['DELETE'],
            'options' => ['OPTIONS'],
        ];
    }

}
