<?php
namespace ff\helpers;

class xmlParser
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

    public function header()
    {
        $response->header('Content-Type','text/xml; charset=utf-8');
        $response->header('Content-Length',strlen($this->incomStrs));
    }

    public function array2xml($arr, $rootNodeName = 'data', $xml = null)
    {
        is_null($xml) && $xml = new \SimpleXMLElement('<xml></xml>');
        foreach ($arr as $key => $val) {
            if (is_numeric($key)) {
                $key = "list_" . (string) $key;
            }
            if (is_array($val) || is_object($val)) {
                $child = $xml->addChild($key);
                $this->array2xml($val, $item, $child);

            } elseif (is_numeric($val)) {
                $child = $xml->addChild($key, $val);
            } else {
                $child = $xml->addChild($key);
                $node = dom_import_simplexml($child);
                $_val = $node->ownerDocument->createCDATASection($val);
                $node->appendChild($_val);
            }
        }
        return $xml->asXML();
    }

    public function parse($incomVars = null)
    {
        $this->incomVars = $incomVars;
        $this->incomStrs = $this->array2xml($incomVars);
    }

}
