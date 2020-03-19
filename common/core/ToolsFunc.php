<?php
use ff\helpers\Dumper;

function view($viewfile)
{
    $view = ff::createObject('ff\base\View');
    $view->cache($viewfile);
    return $view->draw();
}

function viewAssign($varName, $varValue)
{
    $view = ff::createObject('ff\base\View');
    return $view->assign($varName, $varValue);
}

if (!function_exists('dd')) {
    /**
     * Dump the passed variables and end the script.
     *
     * @param  mixed
     * @return void
     */
    function dd(...$args)
    {
        foreach ($args as $x) {
            (new Dumper)->dump($x);
        }
        die(1);
    }
}

if (!function_exists('ddl')) {
    /**
     * Dump the passed variables and end the script.
     *
     * @param  mixed
     * @return void
     */
    function ddl(...$args)
    {
        foreach ($args as $x) {
            (new Dumper)->dump($x);
        }
    }
}

if (!function_exists('ddsql')) {
    /**
     * Dump the passed variables and end the script.
     *
     * @param  mixed
     * @return void
     */
    function ddsql($dbKey = null)
    {
        if(is_null($dbKey)){
            $dasql = \DB::getQueryLog();
        }else{
            $dasql = \DB::connection($dbKey)->getQueryLog(); 
        }
        dd($dasql );
    }
}

if (!function_exists('ddsqlf')) {
    /**
     * Dump the passed variables and end the script.
     *
     * @param  mixed
     * @return void
     */
    function ddsqlf($dbKey = null)
    {

        if(is_null($dbKey)){
            $dasql = \DB::getQueryLog();
        }else{
            $dasql = \DB::connection($dbKey)->getQueryLog(); 
        }
        foreach ($dasql as $one) {
            $sql[] = vsprintf(str_replace('?', "'%s'", $one['query']), $one['bindings']);
        }
        dd($sql );
    }
}


if (!function_exists('dda')) {
    /**
     * Dump the passed array variables and end the script.
     *
     * @param  mixed
     * @return void
     */
    function dda(...$args)
    {
        foreach ($args as $x) {
            (new Dumper)->dump($x->toArray());
        }
        die(1);
    }
}
