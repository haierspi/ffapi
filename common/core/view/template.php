<?php

namespace ff\base;

class view
{

    public $subtemplates = array();
    public $replacecode = array('search' => array(), 'replace' => array());
    public $config = '';
    public $template_ext = '.htm';

    public function __construct($config = array())
    {
        $this->config = $config;
    }

    private function getCacheFilePath($tplfile){
        return SYSTEM_RUNTIME_PATH . '/template/' .  md5($this->tplfile) . '.php';
    }
    private function getTemplateFilePath($tplfile){
        
        $tplfilepath = SYSTEM_VIEWS_PATH . '/' .  $tplfile . $this->template_ext;
        if (!file_exists($tplfilepath)) {
            throw new \Exception(" Unable to find Template File : '{$tplfilepath}' ");
        }
        return $tplfilepath;
    }

    public function cache($tplFile)
    {
        $tplFilePath = $this->getTemplateFilePath($tplFile);
        $cacheFilePath  = $this->getCacheFilePath($tplFilePath);
 
        if (1 || !file_exists($cacheFilePath) || (@filemtime($tplFilePath) > @filemtime($cacheFilePath))) {
            $this->compile($tplFilePath, $cacheFilePath);
        } else {
            $this->compile($tplFilePath, $cacheFilePath);
        }
        return $cacheFilePath;
    }

    public function compile($tplFile, $cacheFile = '')
    {

        if ($fp = @fopen($tplFile, 'r')) {
            $template = @fread($fp, filesize($tplFile));
            fclose($fp);
        } else {
            throw new \Exception("Template File Read Error: '{$tplFile}' ");
        }


        echo '<pre>';
        var_dump( 222 );
        echo '</pre>';
        exit;

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
        $template = preg_replace_callback("/[\n\r\t]*\{eval\}\s*(\<\!\-\-)*(.+?)(\-\-\>)*\s*\{\/eval\}[\n\r\t]*/is", function ($r) {return $this->evaltags($r[2]);}, $template);

        
        $template = preg_replace_callback("/[\n\r\t]*\{eval\s+(.+?)\s*\}[\n\r\t]*/is", function ($r) {return $this->evaltags($r[1]);}, $template);
        $template = str_replace("{LF}", "<?=\"\\n\"?>", $template);
        $template = preg_replace("/\{(\\\$[a-zA-Z0-9_\-\>\[\]\'\"\$\.\x7f-\xff]+)\}/s", "<?=\\1?>", $template);

        $template = preg_replace_callback("/$var_regexp/s", function ($r) {return template::addquote('<?=' . $r[1] . '?>');}, $template);
        $template = preg_replace_callback("/\<\?\=\<\?\=$var_regexp\?\>\?\>/s", function ($r) {return $this->addquote('<?=' . $r[1] . '?>');}, $template);

        $subTplCheckTemplate = '';
        if ($this->subTemplates) {
            foreach ($this->subTemplates as $subTplFile) {
                $subTplCheckTemplate .= "\n\$TP->subTplCheckRefresh('{$subTplFile}','{$tplFile}');";
            }
        }

        $template = "<? if(!defined('SYSTEM_IN')) exit('Access Denied'); $subTplCheckTemplate; \n\$viewContent = <<<EOT\n?>\n$template";

        $template = preg_replace_callback("/[\n\r\t]*\{template\s+([a-z0-9_:\/]+)\}[\n\r\t]*/is", function ($r) {return $this->stripvtags('<? include $TP->cache(\'' . $r[1] . '\'); ?>');}, $template);
        $template = preg_replace_callback("/[\n\r\t]*\{template\s+(.+?)\}[\n\r\t]*/is", function ($r) {return $this->stripvtags('<? include $TP->cache(\'' . $r[1] . '\'); ?>');}, $template);
        $template = preg_replace_callback("/[\n\r\t]*\{echo\s+(.+?)\}[\n\r\t]*/is", function ($r) {return $this->stripvtags('<? echo ' . $this->getphpfilter($r[1]) . ' ?>');}, $template);

        $template = preg_replace_callback("/([\n\r\t]*)\{if\s+(.+?)\}([\n\r\t]*)/is", function ($r) {return $this->stripvtags($r[1] . '<? if(' . $r[2] . ') { ?>' . $r[3]);}, $template);
        $template = preg_replace_callback("/([\n\r\t]*)\{elseif\s+(.+?)\}([\n\r\t]*)/is", function ($r) {return $this->stripvtags($r[1] . '<? } elseif(' . $r[2] . ') { ?>' . $r[3]);}, $template);

        $template = preg_replace("/\{else\}/i", "<? } else { ?>", $template);
        $template = preg_replace("/\{\/if\}/i", "<? } ?>", $template);

        $template = preg_replace_callback("/[\n\r\t]*\{loop\s+(\S+)\s+(\S+)\}[\n\r\t]*/is", function ($r) {return $this->stripvtags('<? if(is_array(' . $r[1] . ')) foreach(' . $r[1] . ' as ' . $r[2] . ') { ?>');}, $template);
        $template = preg_replace_callback("/[\n\r\t]*\{loop\s+(\S+)\s+(\S+)\s+(\S+)\}[\n\r\t]*/is", function ($r) {return $this->stripvtags('<? if(is_array(' . $r[1] . ')) foreach(' . $r[1] . ' as ' . $r[2] . ' => ' . $r[3] . ') { ?>');}, $template);

        $template = preg_replace("/\{\/loop\}/i", "<? } ?>", $template);

        $template = preg_replace("/\{$const_regexp\}/s", "<?=\\1?>", $template);
        if (!empty($this->replacecode)) {
            $template = str_replace($this->replacecode['search'], $this->replacecode['replace'], $template);
        }
        $template = preg_replace("/ \?\>[\n\r]*\<\? /s", " ", $template);

        $template = preg_replace_callback("/\"(http)?[\w\.\/:]+\?[^\"]+?&[^\"]+?\"/", function ($r) {return $this->transamp($r[1]);}, $template);
        $template = preg_replace_callback("/\<script[^\>]*?src=\"(.+?)\"(.*?)\>\s*\<\/script\>/is", function ($r) {return $this->stripscriptamp($r[1], $r[2]);}, $template);

        $template = preg_replace("/\<\?(\s{1})/is", "<?php\\1", $template);
        $template = preg_replace("/\<\?\=(.+?)\?\>/is", "<?php echo \\1;?>", $template);
        $template = "\n<?\nEOT;\n?>";
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

    public function evaltags($php)
    {

        echo '<pre>';
        var_dump(   $php );
        echo '</pre>';
        exit;
        $php = str_replace('\"', '"', $php);


        
        if(!preg_match('/;\h*$/is',$php)){
            $php = preg_replace ('/;\h*$/is','',$php);
            echo '<pre>';
            var_dump( $php  );
            echo '</pre>';
            exit;
            
        }
        $i = count($this->replacecode['search']);
        $this->replacecode['search'][$i] = $search = "<!--EVAL_TAG_$i-->";
        $this->replacecode['replace'][$i] = "<? $php?>";
        return $search;
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
