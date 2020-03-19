<?php
namespace models\v1_0;

use ff\database\db;
use ff\database\Model;

class GoodsSku extends Model
{

    public $table = 'goods_sku';
    protected $primaryKey='sku_id';
    public $timestamps = false;
    
    //查询单条
    public function getOneGoodsSkuInfo($where)
    {
        return (array) $result = DB::table('goods_sku')->where($where)->first();
    }

    //查询单条
    public function getOneGoodsSkuAndGoodsInfo($where)
    {
        return (array) $result = DB::table('goods_sku as gs')
            ->leftJoin('goods as g','g.goods_id','=','gs.goods_id')
            ->select('gs.*','g.goods_sn')
            ->where($where)
            ->first();
    }

    //查询多条
    public function getGoodsSkuInfo($where)
    {
        return (array) $result = DB::table('goods_sku')->where($where)->get()->toArray();
    }
    
    //新增单条
    public function addGoodsSkuInfo($data)
    {
        $result = DB::table('goods_sku')->insertGetId($data);
        return DB::table('goods_sku')->where('id',$result)->first();
    }
    
    //修改单条
    public function setGoodsSkuInfo($where,$data)
    {
        return $result = DB::table('goods_sku')->where($where)->update($data);
    }
}