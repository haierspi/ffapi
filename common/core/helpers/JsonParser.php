<?php
namespace ff\helpers;

class JsonParser
{

    private $incomStrs;
    private $incomVars;

    public function __construct()
    {
    }

    public function __invoke($outVars,$response)
    {
        $this->parse($outVars);
        $this->header($response);
        return $this->incomStrs;
    }

    public function header($response)
    {
        $response->header('Content-Type','application/json;charset=UTF-8');
    }
    public function parse($incomVars = NULL)
    {
        $this->incomVars = $incomVars;
        $this->incomStrs = json_encode($this->incomVars);
    }

    

}
