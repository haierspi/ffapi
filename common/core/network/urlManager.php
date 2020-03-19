<?php
namespace ff\network;

use ff\base\Component;

class urlManager
{
    private $ruleVars = [];
    private $config = [];
    public function __construct($config)
    {
        $this->config = $config;

    }
    public function init($requestPath)
    {
        $this->ruleVars = [];
        $urldata = parse_url($requestPath);
        preg_match('/^\/((v[\d\.]+)\/)?([\w\-\/]+)(\.(\w+))?$/is', $urldata['path'], $match);


        $this->ruleVars['_VERSION'] = $match[2];
        $this->ruleVars['_FORMAT'] = $match[5];
        $_PARAM = explode('/', $match[3]);

        if ($_PARAM) {
            $this->ruleVars['_CONTROLLER'] = $_PARAM[0];
            $this->ruleVars['_ACTION'] = $_PARAM[1];
            unset($_PARAM[0], $_PARAM[1]);
            $this->ruleVars['_ACTION_PARAMS'] = empty($_PARAM) ? [] : array_values($_PARAM);
        }
    }

    public function __get($name)
    {
        if (!isset($this->ruleVars[$name])) {
            throw new Exception('Getting unknown property: ' . get_class($this) . '::' . $name, 0);
        }
        return $this->ruleVars[$name] ?: null;
    }
    public function __isset($name)
    {
        return isset($this->ruleVars[$name]);
    }
}
