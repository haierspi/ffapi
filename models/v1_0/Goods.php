<?php
namespace models\v1_0;
use ff\database\Model;
use ff\helpers\Pager;
use ff\database\DB;
use ff\helpers\IntLib;
use models\v1_0\RecommendRemarksLog;
use models\v1_0\BasicLog;
class Goods extends Model{
    public $table='goods';
    public $timestamps = false;
    protected $primaryKey='goods_id';

    public static function updateData($goods_id,$data,$type=''){
        $info=self::find($goods_id);
        if($type){//推荐打版跟进驳回
            if(!in_array($info->selection_status,[1,4])){
                return false;
            }
        }
        foreach($data as $k=>$v){
            $info->$k=$v;
        }
        $result=$info->save();
        return $result;
    }

    public static function attrUpdate($goods_sn,$data,$user){
        $type=[1=>'新品定制', 3 => '新品推荐', 4=>'采转定',5=> '采转推荐',6=> 'POP熔断款',7=>'LW熔断款' ,10 => '全部'];
        $info=self::where('goods_sn',$goods_sn)->first()->toArray();
        $applyData=[
            'goods_sn'=>$goods_sn,
            'add_time'=>time(),
            'admin_id'=>$user->id,
        ];
        $apply=DB::table('goods_apply')->insertGetId($applyData);

        $arr=[
            ['apply_id'=>$apply,'apply_name'=>'供应商名称','apply_content_1'=>$info['suppliers_name'],'apply_content_2'=>$data['supplierName']],
            ['apply_id'=>$apply,'apply_name'=>'生产方式','apply_content_1'=>$type[$info['type_id']],'apply_content_2'=>$type[$data['recommendStatus']]],
            ['apply_id'=>$apply,'apply_name'=>'采购价格','apply_content_1'=>$info['c_in_price'],'apply_content_2'=>$data['samplePrice']],
            ['apply_id'=>$apply,'apply_name'=>'克重','apply_content_1'=>$info['goods_weight'],'apply_content_2'=>$data['weight']],
            ['apply_id'=>$apply,'apply_name'=>'供应商货号','apply_content_1'=>$info['supp_sn'],'apply_content_2'=>$data['supplierSn']],
        ];
        $result=self::insert($arr);
    }

}