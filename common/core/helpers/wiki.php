<?php

namespace ff\helpers;

class wiki
{

    public $content = '';
    public $parsefile = '';
    public function __construct()
    {

    }
    //parent
    public function getActionWikiData($file, $action)
    {
        $this->getFileContent($file);

        return $this->WikiCache($action);

    }

    public function getFileContent($file)
    {
        if ($this->parsefile != $file) {
            $this->content = file_get_contents($file);
            $this->parsefile = $file;
        }

    }

    public function getControllerName($file = null)
    {

        $file = $file ?: $this->parsefile;
        $this->getFileContent($file);

        preg_match('/\s*\/\*(.*)\*\/\s*\h*class\h*[a-zA-Z0-9\-\_]+Controller\h*/is', $this->content, $match, PREG_OFFSET_CAPTURE);

        $content = substr($this->content, $match[1][1], strlen($match[1][0]));

        $content = preg_replace('/\*?\h*\r\n\h*\*\h*/is', "\r\n", $content);
        $content = trim($content);

        if (strncmp($content, '@name', 5)) {
            return [];
        }
        preg_match('/@name\h*(\H*)\h*(\H*)/is', $content, $match2);

        return [$match2[1], $match2[2]];

    }

    public function getAllActions($file = null)
    {

        $file = $file ?: $this->parsefile;
        $this->getFileContent($file);

        preg_match('/class(.*)\/\*/Uis', $this->content, $match, PREG_OFFSET_CAPTURE);
        $this->content = substr($this->content, strlen($match[1][0]) + $match[1][1]);
        preg_match('/.*(})/is', $this->content, $match, PREG_OFFSET_CAPTURE);
        $this->content = substr($this->content, 0, $match[1][1]);

        preg_match_all('/\s*\/\*(.*)\*\/\s*\h*public\h*function\h*action([a-zA-Z0-9\-\_]+)\h*\(/iUs', $this->content, $matchs);
        $Actions = [];
        foreach ($matchs[1] as $key => $cont) {
            preg_match('/@name\s*([^\n\r]*)\s*\h*/is', $cont, $match);
            preg_match('/(\S*)\s*(\S*)/is', $match[1], $match2);

            $Actions[$matchs[2][$key]] = [
                'action' => $matchs[2][$key],
                'name' => $match2[1],
                'description' => $match2[2],
            ];
        }

        return $Actions;

    }

    public function WikiCache($action)
    {
        return $this->actionWikiParse($action);
    }
    public function actionWikiParse($action)
    {

        $action = strtolower($action);

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
        $match = preg_replace('/(^\*|\n\*)\h*/is', "\n", $match);
        $match = explode("\n", $match);



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
