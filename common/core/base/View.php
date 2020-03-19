<?php

namespace ff\base;

class View
{

    public $subtemplates = array();
    public $replacecode = array('search' => array(), 'replace' => array());
    public $templateExt = '.htm';
    public $vars = [];
    public $cacheFilePath = '';
    

    public function __construct()
    {
    }

    public function assign($varName,$varValue)
    {
        $this->vars[$varName] = $varValue;
    }
    
    public function cache($tplFile)
    {
        $tplFilePath = $this->getTemplateFilePath($tplFile);
        $cacheFilePath = $this->cacheFilePath = $this->getCacheFilePath($tplFilePath);

        if (!file_exists($cacheFilePath) || (@filemtime($tplFilePath) > @filemtime($cacheFilePath))) {
            $this->compile($tplFilePath, $cacheFilePath);
        } else {
            $this->compile($tplFilePath, $cacheFilePath);
        }
        
        return $cacheFilePath;
    }

    public function draw()
    {
        extract($this->vars);
        
        include $this->cacheFilePath;
        return $viewContent;
    }

    public function compile($tplFile, $cacheFile = '')
    {

        if ($fp = @fopen($tplFile, 'r')) {
            $template = @fread($fp, filesize($tplFile));
            fclose($fp);
        } else {
            throw new \Exception("Template File Read Error: '{$tplFile}' ");
        }

        $var_regexp = "((\\\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*(\-\>)?[a-zA-Z0-9_\x7f-\xff]*)(\[[a-zA-Z0-9_\-\.\"\'\[\]\$\x7f-\xff]+\])*)";
        $const_regexp = "([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)";

        $this->subTemplates = array();
        for ($i = 1; $i <= 3; $i++) {
            if (preg_match('/{subtemplate/is', $template)) {
                $template = preg_replace_callback("/[\n\r\t]*(\<\!\-\-)?\{subtemplate\s+([a-z0-9_:\/]+)\}(\-\-\>)?[\n\r\t]*/is", function ($r) {return $this->loadSubTemplate($r[2]);}, $template);
            }
        }
        $template = preg_replace("/([\n\r]+)\t+/s", "\\1", $template);
        $template = preg_replace("/\<\!\-\-\{(.+?)\}\-\-\>/s", "{\\1}", $template);
        $template = preg_replace_callback("/([\n\r\t]*)\{if\s+(.+?)\}([\n\r\t]*)/is", function ($r) {return $this->tag('if', $r[2]);}, $template);
        $template = preg_replace_callback("/([\n\r\t]*)\{elseif\s+(.+?)\}([\n\r\t]*)/is", function ($r) {return $this->tag('elseif', $r[2]);}, $template);
        $template = preg_replace_callback("/\{else\}/i", function ($r) {return $this->tag('else', $r[1]);}, $template);
        $template = preg_replace_callback("/\{\/if\}/i", function ($r) {return $this->tag('endif', $r[1]);}, $template);
        $template = preg_replace_callback("/[\n\r\t]*\{echo\s+(.+?)\}[\n\r\t]*/is", function ($r) {return $this->tag('echo', $r[1]);}, $template);
        $template = preg_replace_callback("/[\n\r\t]*\{eval\}\s*(.+?)\s*\{\/eval\}[\n\r\t]*/is", function ($r) {return $this->tag('eval', $r[1]);}, $template);
        $template = preg_replace_callback("/[\n\r\t]*\{eval\s+(.+?)\s*\}[\n\r\t]*/is", function ($r) {return $this->tag('eval', $r[1]);}, $template);

        $template = preg_replace_callback("/[\n\r\t]*\{loop\s+(\S+)\s+(\S+)\}[\n\r\t]*/is", function ($r) {return $this->tag('loop', $r[1], $r[2]);}, $template);
        $template = preg_replace_callback("/[\n\r\t]*\{loop\s+(\S+)\s+(\S+)\s+(\S+)\}[\n\r\t]*/is", function ($r) {return $this->tag('loop', $r[1], $r[2], $r[3]);}, $template);
        $template = preg_replace_callback("/[\n\r\t]*\{\/loop\}[\n\r\t]*/i", function ($r) {return $this->tag('endloop');}, $template);
        $template = preg_replace_callback("/[\n\r\t]*\{($const_regexp)\}[\n\r\t]*/s", function ($r) {return $this->tag('const', $r[1]);}, $template);

        $template = str_replace("{LF}", "\n", $template);
        $template = preg_replace("/\{(\\\$[a-zA-Z0-9_\-\>\[\]\'\"\$\.\x7f-\xff]+)\}/s", "{\\1}", $template);
        $template = preg_replace_callback("/\{?$var_regexp\}?/s", function ($r) {return $this->addquote('{' . $r[1] . '}');}, $template);
        $subTplCheckTemplate = '';
        if ($this->subTemplates) {
            foreach ($this->subTemplates as $subTplFile) {
                $subTplCheckTemplate .= "\n\$this->subTplCheckRefresh('{$subTplFile}','{$tplFile}');";
            }
        }

        $template = "<?php\nif(!defined('SYSTEM_IN')) exit('Access Denied');\n$subTplCheckTemplate\n\$viewContent .= <<<EOT\n$template";
        $template = preg_replace_callback("/\"(http)?[\w\.\/:]+\?[^\"]+?&[^\"]+?\"/", function ($r) {return $this->transamp($r[1]);}, $template);
        $template = preg_replace_callback("/\<script[^\>]*?src=\"(.+?)\"(.*?)\>\s*\<\/script\>/is", function ($r) {return $this->stripscriptamp($r[1], $r[2]);}, $template);
        $template .= "\nEOT;\n";

        if (!empty($this->replacecode)) {
            $template = str_replace($this->replacecode['search'], $this->replacecode['replace'], $template);
        }

        if (!@$fp = fopen($cacheFile, 'w')) {
            throw new \Exception("Template File Compile Error: '{$cacheFile}' ");
        }

        flock($fp, 2);
        fwrite($fp, $template);
        fclose($fp);
    }

    public function loadSubTemplate($tplFile)
    {

        $tplFilePath = $this->getTemplateFilePath($tplFile);

        if (($content = @implode('', file($tplFilePath)))) {
            $this->subTemplates[] = $tplFilePath;
            return $content;
        } else {
            return '<!-- ' . $tplFile . ' -->';
        }
    }

    private function getCacheFilePath($tplfile)
    {
        return SYSTEM_RUNTIME_PATH . '/template/' . md5($this->tplfile) . '.php';
    }
    private function getTemplateFilePath($tplfile)
    {

        $tplfilepath = SYSTEM_VIEWS_PATH . '/' . $tplfile . $this->templateExt;
        if (!file_exists($tplfilepath)) {
            throw new \Exception("View File Error: '{$tplfilepath}' ");
        }
        return $tplfilepath;
    }

    public function subTplCheckRefresh($subTplFilePath, $tplFilePath)
    {
        $cacheFilePath = $this->getCacheFilePath($tplFilePath);
        if (file_exists($subTplFilePath)) {
            if ((@filemtime($subTplFilePath) > @filemtime($tplFilePath))) {
                $this->compile($tplFilePath, $cacheFilePath);
            }
        }
    }

    public function getphptemplate($content)
    {
        $pos = strpos($content, "\n");
        return $pos !== false ? substr($content, $pos + 1) : $content;
    }

    public function getphpfilter($content)
    {

        if (preg_match('/[^;]\s*$/is', $content)) {
            $content = trim($content) . ';';
        }
        return $content;
    }

    public function tag($mode, $phpcode1 = '', $phpcode2 = '', $phpcode3 = '')
    {

        $phpcode1 = str_replace('\"', '"', $phpcode1);
        $phpcode2 = str_replace('\"', '"', $phpcode2);
        $phpcode3 = str_replace('\"', '"', $phpcode3);
        $i = count($this->replacecode['search']);

        if ($mode == 'if') {

            $phpcode1 = preg_replace_callback("/([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\[([a-zA-Z0-9_\x7f-\xff]+)\]/s", function ($r) {return $this->arrayfilter($r[1], $r[2]);}, $phpcode1);

            $replace = "\nEOT;\nif({$phpcode1}) { \n\$viewContent .= <<<EOT\n";
        } elseif ($mode == 'else') {
            $replace = "\nEOT;\n} else { \n\$viewContent .= <<<EOT\n";
            //22
        } elseif ($mode == 'elseif') {
            $phpcode1 = preg_replace_callback("/([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\[([a-zA-Z0-9_\x7f-\xff]+)\]/s", function ($r) {return $this->arrayfilter($r[1], $r[2]);}, $phpcode1);
           
            $replace = "\nEOT;\n} elseif ({$phpcode1}) { \n\$viewContent .= <<<EOT\n";
        } elseif ($mode == 'endif') {
            $replace = "\nEOT;\n} \n\$viewContent .= <<<EOT\n";
        } elseif ($mode == 'echo') {
            $phpcode1 = $this->tag_addcolon($phpcode1);
            $replace = "\nEOT;\n\$viewContent .= {$phpcode1}; \n\$viewContent .= <<<EOT\n";
        } elseif ($mode == 'eval') {
            $phpcode1 = $this->tag_addcolon($phpcode1);
            $replace = "\nEOT;\n{$phpcode1}\n\$viewContent .= <<<EOT\n";
        } elseif ($mode == 'loop') {
            $phpcode1 = preg_replace_callback("/([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\[([a-zA-Z0-9_\x7f-\xff]+)\]/s", function ($r) {return $this->arrayfilter($r[1], $r[2]);}, $phpcode1);

            if ($phpcode3) {
                $foreachkeyvaluestatement = $phpcode2 . '=>' . $phpcode3;
            } else {
                $foreachkeyvaluestatement = $phpcode2;
            }
            $replace = "\nEOT;\nif(is_array( {$phpcode1})) { \nforeach({$phpcode1} as {$foreachkeyvaluestatement}) { \n\$viewContent .= <<<EOT\n";
        } elseif ($mode == 'endloop') {
            $replace = "\nEOT;\n}\n}\n\$viewContent .= <<<EOT\n";
        } elseif ($mode == 'const') {
            $replace = "\nEOT;\n\$viewContent .= defined('{$phpcode1}')?constant('{$phpcode1}'):''; \n\$viewContent .= <<<EOT\n";
        }

        $this->replacecode['search'][$i] = $search = "<!--" . strtoupper($mode) . "_TAG_$i-->";
        $this->replacecode['replace'][$i] = $replace;
        return $search;
    }

    public function tag_addcolon($phpcode){
        if(!preg_match('/;\s*$/is',$phpcode)){
            $phpcode = preg_replace ('/\s*$/is',';',$phpcode);
        }
        return $phpcode;
    }

    public function arrayfilter($str1,$str2)
    {
        return $str1."['".$str2."']";
    }

    public function transamp($str)
    {
        $str = str_replace('&', '&amp;', $str);
        $str = str_replace('&amp;amp;', '&amp;', $str);
        $str = str_replace('\"', '"', $str);
        return $str;
    }

    public function addquote($var)
    {
        return str_replace("\\\"", "\"", preg_replace("/\[([a-zA-Z0-9_\-\.\x7f-\xff]+)\]/s", "['\\1']", $var));
    }

    public function stripvtags($expr, $statement = '')
    {
        $expr = str_replace("\\\"", "\"", preg_replace("/\<\?\=(\\\$.+?)\?\>/s", "\\1", $expr));
        $statement = str_replace("\\\"", "\"", $statement);
        return $expr . $statement;
    }

    public function stripscriptamp($s, $extra)
    {
        $extra = str_replace('\\"', '"', $extra);
        $s = str_replace('&amp;', '&', $s);
        return "<script src=\"$s\" type=\"text/javascript\"$extra></script>";
    }

    public function error($message, $tplname)
    {
        core_error::template_error($message, $tplname);
    }
    public function fileext($filename)
    {
        return addslashes(strtolower(substr(strrchr($filename, '.'), 1, 10)));
    }

}
