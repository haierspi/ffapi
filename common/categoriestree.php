<?php
namespace common;

use ff\database\db;

class categoriestree
{
    public $originaldata = [];
    public $originalsupdata = [];
    public $treedata = [];
    public $suptreedata = [];
    public $rellist = [];

    //加载原生数据
    public function initdata()
    {
        $query = DB::query("SELECT `cateid`,`title`,`fid`,`level`,`gmid` FROM " . DB::table('category') . ' ORDER BY `fid` ASC, `order` DESC');
        while ($oneRecord = DB::fetch($query)) {
            $this->originaldata[$oneRecord['cateid']] = $oneRecord;
        }

        $query = DB::query("SELECT * FROM " . DB::table('supplier_category'));
        while ($oneRecord = DB::fetch($query)) {
            $this->originalsupdata[$oneRecord['scateid']] = $oneRecord;
        }
    }

    //获取树状结构
    public function gettree()
    {
        foreach ($this->originalsupdata as $cateid => $oneRecord) {
            if ($oneRecord) {
                $categorysuptreedata[$oneRecord['cateid']][] = $oneRecord;
            }
        }

        foreach ($this->originaldata as $cateid => $oneRecord) {
            if ($oneRecord['level'] == 3) {
                if (isset($categorysuptreedata[$oneRecord['cateid']])) {
                    $oneRecord['supcategories'] = $categorysuptreedata[$oneRecord['cateid']];
                } else {
                    $oneRecord['supcategories'] = [];
                }
            }

            $treedata[$oneRecord['cateid']] = $oneRecord;
            $treedata[$oneRecord['fid']]['subcategories'][] = &$treedata[$oneRecord['cateid']];
        }

        $this->treedata = $treedata;

    }

    //获取关系铺开清单
    public function rellist()
    {
        $catelist = $rellist = [];
        foreach ($this->originaldata as $cateid => $oneRecord) {

            $catelist[$oneRecord['fid']][$oneRecord['cateid']] = $oneRecord;
        }

        foreach ($catelist[0] as $cateid => $oneRecord) {
            $rellist[$cateid] = $oneRecord;
            $rellist = array_merge($rellist, $this->relsublist($oneRecord['cateid'], $catelist));
        }

        $this->rellist = $rellist;
    }

    public function relsublist($cateid, $catelist)
    {
        $rellist = [];

        if (is_array($catelist[$cateid])) {
            foreach ($catelist[$cateid] as $subcateid => $oneRecord) {
                $rellist[] = $oneRecord;
                if ($oneRecord['level'] < 3) {
                    $rellist = array_merge($rellist, $this->relsublist($oneRecord['cateid'], $catelist));
                }
            }
        }

        return $rellist;

    }

    //获取面包屑清单
    public function crumbs($cateid)
    {
        $crumbs = [];
        $current = $this->current($cateid);
        if ($current['fid']) {
            $crumbs = array_merge($this->crumbs($current['fid']), $crumbs);
        }
        $crumbs[$current['cateid']] = $current;
        return $crumbs;
    }

    //获取当前分类
    public function current($cateid)
    {
        return $this->originaldata[$cateid] ?? null;
    }

    //获取当前父分类ID
    public function parentid($cateid)
    {
        $categories = $this->current($cateid);
        return $categories['fid'] ?? null;
    }
    //获取父分类
    public function parent($cateid)
    {
        $categories = $this->current($cateid);
        $fid = $categories['fid'];
        return $this->originaldata[$fid] ?? null;

    }
    //获取所有父分类
    public function parents($cateid, $self = false)
    {

        $parents = [];
        if ($self) {
            $parents[] = $this->current($cateid);
        }
        do {
            $categories = $this->parent($cateid);
            $cateid = $categories['cateid'];
            if ($categories) {
                $parents[] = $categories;
            }
        } while ($categories);

        return $parents;

    }

    //清理分类
    public function copy($category)
    {

        foreach($category['subcategories'] as $key=>$subcategory){
            
        }

        return $categories;

    }

    //清理分类
    public function filtersub($category)
    {


        if($category['scateid']){
            return $category;
        }
        
        if(!$category['subcategories']){
            return [];
        }

        $returnval = FALSE;
        foreach($category['subcategories'] as $key=>$subcategory){
            $category['subcategories'][$key] = $this->filtersub($subcategory);
            echo '<pre>';
            var_dump(  $category['subcategories'][$key] );
            echo '</pre>';
            exit;
            
            if($category['subcategories'][$key]){
               // unset($category['subcategories'][$key]);
            }
        }
        return $category;

    }

    //获取兄弟分类ID
    public function siblingids($cateid, $self = false)
    {
        $siblingids = [];
        if ($self) {
            $siblingids[] = $cateid;
        }
        $parentid = $this->parentid($cateid);
        foreach ($this->originaldata as $cateid => $categories) {
            if ($parentid == $categories['fid']) {
                $siblingids[] = $cateid;
            }
        }

        return $siblingids;

    }
    //获取兄弟分类
    public function siblings($cateid, $self = false)
    {
        $siblings = [];
        $siblingids = $this->siblingids($cateid, $self);
        foreach ($siblingids as $cateid) {
            $siblings[] = $this->current($cateid);
        }
        return $siblings;

    }

    //获取供应商当前分类
    public function scurrent($scateid)
    {
        return $this->originalsupdata[$scateid] ?? null;
    }
    //获取供应商兄弟分类
    public function ssiblings($scateid, $self = false)
    {
        //获取当前供应商分类对应的供应商分类
        $cateid = $this->scateidcateid($scateid);
        $supid = $this->scateidsupid($scateid);
        $siblingids = $this->siblingids($cateid);

        $ssiblings = [];

        foreach ($this->originalsupdata as $supdata) {

            if ($supdata['supid'] == $supid && in_array($cateid, $siblingids)) {
                $supdata['self'] = '0';
                if ($self && $supdata['scateid'] == $scateid) {
                    $supdata['self'] = '1';
                }
                $supdata['categorytitle'] = $this->current($supdata['cateid'])['title'];

                $ssiblings[] = $supdata;
            }
        }
        return $ssiblings;

    }

    public function scateidcateid($scateid)
    {
        return $this->originalsupdata[$scateid]['cateid'];
    }

    public function scateidsupid($scateid)
    {
        return $this->originalsupdata[$scateid]['supid'];
    }

    public function sparent($scateid)
    {
        $cateid = $this->scateidconvert($scateid);
        return $this->parent($cateid);
    }

    public function sparents($scateid, $self = false)
    {
        $cateid = $this->scateidconvert($scateid);
        return $this->parents($cateid, $self);
    }

}
