<?php
namespace common\logicalentity;

use models\tables\Scm as ScmModel;
use models\tables\ScmPDemands;
use models\v1_0\GoodsSku;

class Scm
{
    /**
     *
     * @name  获取SCM采购待处理中的sku对应采购商品数量
     * @author gaojunhua
     *
     */
    public function getSkuToNum($return)
    {
        $goodsSkuObj = new GoodsSku();
        $scmObj = new ScmModel();
        $scmPDemandsObj = new ScmPDemands();
        $total = [];
        
        //获取全部的订单分配待处理数据，合并成以scm_id为维度的数组
        $scmPDemandsInfo = $scmPDemandsObj->getScmPDemandsInfo([
            ['status', '=', 0],
            ['is_del', '=', 0],
        ]);
        foreach($scmPDemandsInfo as $oneScmPDemandsInfo) {
            $newSize = json_decode($oneScmPDemandsInfo->size, true);

            if($total[$oneScmPDemandsInfo->scm_id])
            {
                //合并尺码数据
                $array_merge = array_merge_recursive($total[$oneScmPDemandsInfo->scm_id], $newSize);
                foreach($array_merge as $key => $value) {
                    if(is_array($value))
                        $array_merge[$key] = array_sum($value);
                }
                $total[$oneScmPDemandsInfo->scm_id] = $array_merge;
            } else {
                $total[$oneScmPDemandsInfo->scm_id] = $newSize;
            }
        }

        //便利全部的scmid，获取对应的货号，并根据货号和尺码合并成sku_num_sn维度的数据
        foreach($total as $scmId => $sizeList) {
            $scmInfo = $scmObj->getOneScmInfo([
                ['id', '=', $scmId],
                ['product_status', '<>', 3],
                ['is_draft', '=', 0],
                ['is_del', '=', 0],
            ]);
            $goodsId = $scmInfo['goods_id'];

            foreach($sizeList as $size => $num) {
                $skuInfo = $goodsSkuObj->getOneGoodsSkuAndGoodsInfo([
                    ['gs.goods_id', '=', $goodsId],
                    ['gs.size_en', '=', $size],
                    ['gs.is_delete', '=', 0],
                ]);
                if(empty($skuInfo)) {
                    continue;
                }
                $skuNumSn = $skuInfo['sku_num_sn'];

                if(isset($return[$skuInfo['goods_sn']][$skuNumSn])) {
                    $return[$skuInfo['goods_sn']][$skuNumSn]['num'] += $num;
                    $return[$skuInfo['goods_sn']][$skuNumSn]['scm_num'] += $num;
                } else {
                    $return[$skuInfo['goods_sn']][$skuNumSn]['num'] = $num;
                    $return[$skuInfo['goods_sn']][$skuNumSn]['scm_num'] = $num;
                    $return[$skuInfo['goods_sn']][$skuNumSn]['1688_num'] = 0;
                    $return[$skuInfo['goods_sn']][$skuNumSn]['puyuan_num'] = 0;
                }
            }
        }
        return $return;
    }


    /**
     *
     * @name  根据sku获取sku下SCM采购待处理中的采购商品数量
     * @author gaojunhua
     *
     */
    public function getSkuOrderNumBySku($skuNumSn)
    {
        $goodsSkuObj = new GoodsSku();
        $scmObj = new ScmModel();
        $scmPDemandsObj = new ScmPDemands();
        $total = 0;

        $skuInfo = $goodsSkuObj->getOneGoodsSkuInfo([
            ['sku_num_sn', '=', $skuNumSn],
            ['is_delete', '=', 0],
        ]);

        //获取到需要的 商品id和对应尺码
        $goodsId = $skuInfo['goods_id'];
        $size = $skuInfo['size_en'];

        //可能会有多条
        $scmInfo = $scmObj->getScmInfo([
            ['goods_id', '=', $goodsId],
            ['product_status', '<>', 3],
            ['is_draft', '=', 0],
            ['is_del', '=', 0],
        ]);

        foreach($scmInfo as $oneScmInfo) {
            //获取需要的信息
            $scmId = $oneScmInfo->id;

            $scmPDemandsInfo = $scmPDemandsObj->getScmPDemandsInfo([
                ['scm_id', '=', $scmId],
                ['is_del', '=', 0],
            ]);

            foreach($scmPDemandsInfo as $oneScmPDemandsInfo) {
                $sizeList = json_decode($oneScmPDemandsInfo->size, true);
                $total += $sizeList[$size];
            }
        }
        return $total;
    }

    /**
     *
     * @name  根据SKU获取该SKU在SCM采购待处理的个数
     * @author gaojunhua
     *
     */
    public function getOrderNumInScmPDemandsBySku($skuNumSn)
    {
        $goodsSkuObj = new GoodsSku();
        $scmObj = new ScmModel();
        $scmPDemandsObj = new ScmPDemands();
        $total = 0;

        $skuInfo = $goodsSkuObj->getOneGoodsSkuInfo([
            ['sku_num_sn', '=', $skuNumSn],
            ['is_delete', '=', 0],
        ]);

        //获取到需要的 商品id和对应尺码
        $goodsId = $skuInfo['goods_id'];
        $size = $skuInfo['size_en'];

        //可能会有多条
        $scmInfo = $scmObj->getScmInfo([
            ['goods_id', '=', $goodsId],
            ['product_status', '<>', 3],
            ['is_draft', '=', 0],
            ['is_del', '=', 0],
        ]);

        foreach($scmInfo as $oneScmInfo) {
            //获取需要的信息
            $scmId = $oneScmInfo->id;

            $scmPDemandsCount = $scmPDemandsObj->getScmPDemandsCount([
                ['scm_id', '=', $scmId],
                ['is_del', '=', 0],
            ]);
            if($scmPDemandsCount == 0){
                continue;
            }
            $total += $scmPDemandsCount;
        }
        return $total;
    }

    /**
     *
     * @name   根据SKU获取货号在SCM采购待处理中的采购商品总数量
     * @author gaojunhua
     *
     */
    public function getNumInScmPDemandsBySku($skuNumSn)
    {
        $goodsSkuObj = new GoodsSku();
        $scmObj = new ScmModel();
        $scmPDemandsObj = new ScmPDemands();
        $total = 0;

        $skuInfo = $goodsSkuObj->getOneGoodsSkuInfo([
            ['sku_num_sn', '=', $skuNumSn],
            ['is_delete', '=', 0],
        ]);

        //获取到需要的 商品id和对应尺码
        $goodsId = $skuInfo['goods_id'];
        $size = $skuInfo['size_en'];

        //可能会有多条
        $scmInfo = $scmObj->getScmInfo([
            ['goods_id', '=', $goodsId],
            ['product_status', '<>', 3],
            ['is_draft', '=', 0],
            ['is_del', '=', 0],
        ]);

        foreach($scmInfo as $oneScmInfo) {
            //获取需要的信息
            $scmId = $oneScmInfo->id;

            $scmPDemandsInfo = $scmPDemandsObj->getScmPDemandsInfo([
                ['scm_id', '=', $scmId],
                ['is_del', '=', 0],
            ]);

            foreach($scmPDemandsInfo as $oneScmPDemandsInfo) {
                $total += $oneScmPDemandsInfo->total;
            }
        }
        return $total;
    }

            /**
     *
     * @name  根据货号SN 获取在SCM采购待处理中的订单个数
     * @author gaojunhua
     *
     */
    public function getOrderNumInScmPDemandsByGoodsSn($goodsSn)
    {   
        $scmObj = new ScmModel();
        $scmPDemandsObj = new ScmPDemands();
        //可能会有多条
        $scmInfo = $scmObj->getScmInfo([
            ['goods_sn', '=', $goodsSn],
            ['product_status', '<>', 3],
            ['is_draft', '=', 0],
            ['is_del', '=', 0],
        ]);

        foreach($scmInfo as $oneScmInfo) {
            //获取需要的信息
            $scmId = $oneScmInfo->id;

            $scmPDemandsCount = $scmPDemandsObj->getScmPDemandsCount([
                ['scm_id', '=', $scmId],
                ['is_del', '=', 0],
            ]);
            if($scmPDemandsCount == 0){
                continue;
            }
            $total += $scmPDemandsCount;
        }
        return $total;
    }

            /**
     *
     * @name  根据货号SN 获取在SCM采购待处理中的采购商品总数量
     * @author gaojunhua
     *
     */
    public function getNumInScmPDemandsByGoodsSn($goodsSn)
    {
        $scmObj = new ScmModel();
        $scmPDemandsObj = new ScmPDemands();
        //可能会有多条
        $scmInfo = $scmObj->getScmInfo([
            ['goods_sn', '=', $goodsSn],
            ['product_status', '<>', 3],
            ['is_draft', '=', 0],
            ['is_del', '=', 0],
        ]);

        foreach($scmInfo as $oneScmInfo) {
            //获取需要的信息
            $scmId = $oneScmInfo->id;

            $scmPDemandsInfo = $scmPDemandsObj->getScmPDemandsInfo([
                ['scm_id', '=', $scmId],
                ['is_del', '=', 0],
            ]);

            foreach($scmPDemandsInfo as $oneScmPDemandsInfo) {
                $total += $oneScmPDemandsInfo->total;
            }
        }
        return $total;
    }


}