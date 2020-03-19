<?php
namespace ff\helpers;

use ff;
class Pager
{
    public $page;
    public $pageNum;
    public $totalCountFunc;

    public function __construct()
    {
        $this->init();
    }
    public function init()
    {
        $this->page = (int) (ff::$app->router->request->vars['page'] ?: 1);
        $this->pageNum = (int) (ff::$app->router->request->vars['pageNum'] ?: 10);
    }
    public function getLimit()
    {
        return  $this->pageNum;
    }

    public function getOffset()
    {
        return $this->pageNum * ($this->page - 1);
    }
    public function getLimitSql()
    {
        $offsetSql = '';
        if (!is_null($this->pageNum)) {
            $offset = $this->pageNum * ($this->page - 1);
            if ($offset < 0) {
                return [];
            } else {
                $offsetSql = "LIMIT {$offset}, {$this->pageNum}";
            }
        }
        return  $offsetSql;
    }
    public function getData()
    {
        $totalCountFunc = $this->totalCount;
        return  [
            'page'=>$this->page,
            'pageNum'=>$this->pageNum,
            'totalCount'=>$totalCountFunc(),
        ];
    }

}
