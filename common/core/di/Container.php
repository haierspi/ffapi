<?php


namespace ff\di;

use ff\base\Exception;
use ReflectionClass;

class Container
{

    private $_objects = [];
    private $_params = [];
    private $_reflections = [];

    public function get( $className,  $params = [])
    {
        if (isset($this->_objects[$className])) {
            return $this->_objects[$className];
        } else {
            return $this->build($className, $params);
        }

    }

    public function set( $className,  $params = [])
    {
        return $this->build($className, $params);
    }

    public function has( $className)
    {
        return isset($this->_objects[$className]);
    }

    public function clear( $className)
    {
        unset($this->_objects[$className], $this->_params[$class]);
    }

    protected function build( $className, $params)
    {
        $class = new \ReflectionClass($className);
        $this->_objects[$className] = $class->newInstanceArgs($params);
        $this->_reflections[$className] = $class;
        $this->_params[$className] = $params;
        return $this->_objects[$className];
    }
}
