<?php
namespace common\office\type;

use common\logicalentity\Scm as ScmLogic;
use common\office\ExportBase;
use common\office\ExportBaseType;
use ff;
use models\tables\Goods;
use models\tables\GoodsSku;
use models\tables\PySyncDataGoodsSkuStockWarning;
use models\tables\SuppliersNew;

class DIGoodsSkuAnalysis extends ExportBase implements ExportBaseType
{
    public function content()
    {

        $this->spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

        //导入数据字段
        $this->importNormalFields([
            'field1' => '商品编码',
            'field2' => '产品状态',
            'field3' => '在售天数',
            'field4' => '新老品',
            'field5' => '首次上架时间',
            'field6' => '开发时间',
            'field7' => '开发年份',
            'field8' => '季节',
            'field9' => '生产方式',
            'field10' => '供应商',
            'field11' => '大类',
            'field12' => '二类',
            'field13' => '商品单价',
            'field14' => '净库存金额',
            'field15' => '净库存',
            'field16' => '采购未入',
            'field17' => '在仓库存',
            'field18' => '在仓缺货',
            'field19' => '7日销量',
            'field20' => '7日均销',
            'field21' => '昨日销',
            'field22' => '周转天数（按7日均销）',
            'field23' => '产品线',
            'field24' => '置换款号',
            'field25' => '是否挂预售',
            'field26' => '0227供应链无法承接—卖完下架款',
            'field27' => 'scm订单量；待确定',
            'field28' => '销售判断',
            'field29' => '可销天判断',
            'field30' => '在仓库存分类',
            'field31' => '在仓缺货',
            'field32' => '可售在库',
            'field33' => '置换前产品线',
            'field34' => '1.31lw上架pop库存款',
            'field35' => '未采购款',
            'field36' => '在仓可销天',
            'field37' => '在仓可销天分类',
            'field38' => '在仓可销天分类2',
            'field39' => '7日销售分类',
            'field40' => '昨日销售分类',
            'field41' => '在仓量分类',
            'field42' => '7日内回货量',
            'field43' => '7天回货后可销天',
            'field44' => '商品建议销售等级',
            'field45' => '商品建议销售等级',
            'field46' => '3日内发货',
            'field47' => '3日内回货可销天',
            'field48' => '商品建议销售等级(3日内回货)',
            'field49' => '商品建议销售等级(3日内回货)',
            'field50' => '7日回货覆盖缺货',
        ]);

        ff::$app->exportData['dataSyncCount'] = 0;

        $scmLogic = new ScmLogic();

        $resultData = [];
        $row = 2;
        $exportData = [];
        PySyncDataGoodsSkuStockWarning::whereIn('StoreID', [17, 47])
            ->orderBy('id')
            ->chunk(500, function ($list) use ($scmLogic, &$exportData, &$row) {

                $startRow = $row;

                foreach ($list as $oneData) {

                    //排除清仓停售状态中（净库存预计可用库存=0，采购未入=0，在仓库存<=0 同时符合）
                    if ($oneData->hopeUseNum == 0 && $oneData->NotInStore == 0 && ($oneData->hopeUseNum - $oneData->NotInStore) <= 0) {
                        continue;
                    }

                    //获取货号信息
                    $goodsData = Goods::where('goods_sn', $oneData->goodscode)->first();

                    $goodsSkuData = GoodsSku::where(
                        [
                            ['sku_num_sn', $oneData->SKU],
                            ['is_delete', 0],
                        ]
                    )->first();

                    //排除 oms无商品数据
                    if (empty($goodsSkuData)) {
                        continue;
                    }

                    $supplierData = SuppliersNew::where('id', $goodsData->suppliers_id)->first();

                    //判断大码
                    if ($goodsData->parent_goods_id) {
                        $goodsParentData = Goods::where('goods_id', $goodsData->parent_goods_id)->first();
                    }
                    //在售天数
                    if ($goodsData->on_sale_time) {
                        $field3 = ff\helpers\Time::diffBetweenTwoDays(date("Y-m-d H:i:s"), date("Y-m-d H:i:s", $goodsData->on_sale_time));

                        //新老品
                        $field4 = ($field3 <= 15) ? '新新品' : (($field3 <= 30) ? '新新品' : '老品');

                        //首次上架时间 (OMS再次上架时间)
                        $field5 = date("Y-m-d H:i:s", $goodsData->on_sale_time);
                    } else {
                        $field3 = '';
                        $field4 = '';
                        $field5 = '';
                    }
                    //开发时间
                    $field6 = $goodsData->create_time ? date("Y-m-d H:i:s", $goodsData->create_time) : '';

                    //开发年份
                    $field7 = $goodsData->create_time ? date("Y", $goodsData->create_time) : '';

                    //季节
                    $seasonTables = [
                        '1' => '春',
                        '2' => '夏',
                        '3' => '秋',
                        '4' => '冬',
                    ];
                    $field8 = $seasonTables[$goodsData->season];
                    //生产方式
                    $typeTables = [
                        1 => '定制',
                        2 => '采买',
                        3 => '推荐(开发推荐)',
                        4 => '供应推荐',
                    ];
                    $field9 = $typeTables[$goodsData->type_id];

                    //供应商
                    $field10 = $supplierData['abbreviation'];

                    //分类
                    $field11;
                    $field12;

                    //商品单价
                    $field13 = $goodsData->in_price;
                    //净库存金额,
                    $field14 = $goodsData->in_price * $oneData->hopeUseNum; //净库存金额,
                    //净库存,
                    $field15 = $oneData->hopeUseNum;
                    //采购未入,
                    $field16 = $oneData->NotInStore;
                    //在仓库存,
                    $field17 = $field15 - $field16;
                    //在仓缺货,
                    $field18 = $field17;
                    //7日销量,
                    $field19 = $oneData->SellCount1;
                    //7日均销
                    $field20 = $oneData->SellCount1 / 7;
                    //昨日销,
                    $field21 = '';
                    //周转天数（按7日均销）,
                    $field22 = $field20 ? ($oneData->hopeUseNum / $field20) : 0;
                    //产品线,
                    $field23 = $goodsData->product_line;
                    //置换款号,
                    $field24 = $goodsParentData->goods_sn;

                    //是否挂预售
                    $advanceSaleTables = [
                        '1' => '是',
                        '2' => '否',
                    ];
                    $field25 = $advanceSaleTables[$goodsData->advance_sale];

                    //scm订单量
                    $field27 = $scmLogic->getNumInScmPDemandsBySku($oneData->SKU);

                    //七日日均销量POP大于等于3件  LW大于等于1件
                    if ($goodsData->product_line == 'pop') {
                        $field28 = ($oneData->SellCount1 / 7) >= 3 ? '成功款' : '非成功款';
                    } elseif ($goodsData->product_line == 'lw') {
                        $field28 = ($oneData->SellCount1 / 7) >= 1 ? '成功款' : '非成功款';
                    }

                    //可销天判断
                    if ($field22 <= 0) {
                        $field29 = '7天无销售';
                    } elseif ($field22 > 0 && $field22 <= 15) {
                        $field29 = '<15天';
                    } elseif ($field22 > 15 && $field22 <= 30) {
                        $field29 = '15~30天';
                    } elseif ($field22 > 30 && $field22 <= 45) {
                        $field29 = '30~45天';
                    } elseif ($field22 > 45 && $field22 <= 60) {
                        $field29 = '45~60天';
                    } elseif ($field22 > 60 && $field22 <= 90) {
                        $field29 = '60~90天';
                    } elseif ($field22 > 90) {
                        $field29 = '>90天';
                    }
                    //在仓库存分类
                    $field30 = $field17 < 10 ? '在仓<10件' : '在仓>=10件';
                    //在仓缺货
                    $field31 = $field17 > 0 ? '不缺货' : '缺货';
                    //可售在库
                    $field32 = $field17 > 0 ? '可售在库' : '在仓无货';
                    //置换前产品线
                    $field33 = $goodsParentData->product_line;

                    //未采购款,
                    $field35 = $field15 < 0 ? '未采购' : '非未采购';
                    //在仓可销天,
                    $field36 = $field20 ? ($field17 / $field20) : 0;
                    //在仓可销天分类,
                    $field37 = $field37;
                    //可销天判断
                    if ($field36 <= 0) {
                        $field37 = '7天无销售';
                    } elseif ($field36 > 0 && $field36 <= 3) {
                        $field37 = '<=3天';
                    } elseif ($field36 > 0 && $field36 <= 7) {
                        $field37 = '3~7天';
                    } elseif ($field36 > 7 && $field36 <= 15) {
                        $field37 = '7~15天';
                    } elseif ($field36 > 15 && $field36 <= 30) {
                        $field37 = '15~30天';
                    } elseif ($field36 > 30) {
                        $field37 = '>90天';
                    }

                    //在仓可销天分类2,
                    $field38 = $field38;
                    //7日销售分类,
                    if ($field19 < 1) {
                        $field39 = '<1';
                    } elseif ($field19 >= 1 && $field19 < 3) {
                        $field39 = '<=3';
                    } elseif ($field19 >= 3 && $field19 < 5) {
                        $field39 = '3~7';
                    } elseif ($field19 >= 5) {
                        $field39 = '>=5';
                    }

                    //昨日销售分类,
                    if ($field21 < 1) {
                        $field40 = '<1';
                    } elseif ($field21 >= 1 && $field21 < 3) {
                        $field40 = '<=3';
                    } elseif ($field21 >= 3 && $field21 < 5) {
                        $field40 = '3~7';
                    } elseif ($field21 >= 5) {
                        $field40 = '>=5';
                    }
                    //在仓量分类,

                    if ($field17 <= 10) {
                        $field41 = '<=10';
                    } elseif ($field17 > 10 && $field17 <= 20) {
                        $field41 = '10~20';
                    } elseif ($field17 > 20 && $field17 <= 50) {
                        $field41 = '20~50';
                    } elseif ($field17 > 50 && $field17 <= 100) {
                        $field41 = '50~100';
                    } elseif ($field17 > 100) {
                        $field41 = '>100';
                    }
                    //7日内回货量,
                    $field42 = '';
                    //7天回货后可销天,
                    $field43 = '';
                    //商品建议销售等级,
                    $field44 = '';
                    //商品建议销售等级,
                    $field45 = '';
                    //3日内发货,
                    $field46 = '';
                    //3日内回货可销天,
                    $field47 = '';
                    //商品建议销售等级(3日内回货),
                    $field48 = '';
                    //商品建议销售等级(3日内回货),
                    $field49 = '';
                    //7日回货覆盖缺货,
                    $field50 = '';

                    $exportOneData = [];
                    $exportOneData['field1'] = $oneData['SKU']; //商品编码,
                    $exportOneData['field2'] = $oneData['GoodsStatus']; //产品状态,
                    $exportOneData['field3'] = $field3; //在售天数,
                    $exportOneData['field4'] = $field4; //新老品,
                    $exportOneData['field5'] = $field5; //首次上架时间,
                    $exportOneData['field6'] = $field6; //开发时间,
                    $exportOneData['field7'] = $field7; //开发年份,
                    $exportOneData['field8'] = $field8; //季节,
                    $exportOneData['field9'] = $field9; //生产方式,
                    $exportOneData['field10'] = $field10; //供应商,
                    $exportOneData['field11'] = $field11; //大类,
                    $exportOneData['field12'] = $field12; //二类,
                    $exportOneData['field13'] = $field13; //商品单价,
                    $exportOneData['field14'] = $field14; //净库存金额,
                    $exportOneData['field15'] = $field15; //净库存,
                    $exportOneData['field16'] = $field16; //采购未入,
                    $exportOneData['field17'] = $field17; //在仓库存,
                    $exportOneData['field18'] = $field18; //在仓缺货,
                    $exportOneData['field19'] = $field19; //7日销量,
                    $exportOneData['field20'] = $field20; //7日均销,
                    $exportOneData['field21'] = ''; //昨日销,
                    $exportOneData['field22'] = $field22; //周转天数（按7日均销）,
                    $exportOneData['field23'] = $field23; //产品线,
                    $exportOneData['field24'] = $field24; //置换款号,
                    $exportOneData['field25'] = $field25; //是否挂预售,
                    $exportOneData['field26'] = ''; //0227供应链无法承接—卖完下架款,
                    $exportOneData['field27'] = $field27; //scm订单量；待确定,
                    $exportOneData['field28'] = $field28; //销售判断,
                    $exportOneData['field29'] = $field29; //可销天判断,
                    $exportOneData['field30'] = $field30; //在仓库存分类,
                    $exportOneData['field31'] = $field31; //在仓缺货,
                    $exportOneData['field32'] = $field32; //可售在库,
                    $exportOneData['field33'] = $field33; //置换前产品线,
                    $exportOneData['field34'] = $field34; //1.31lw上架pop库存款,
                    $exportOneData['field35'] = $field35; //未采购款,
                    $exportOneData['field36'] = $field36; //在仓可销天,
                    $exportOneData['field37'] = $field37; //在仓可销天分类,
                    $exportOneData['field38'] = $field38; //在仓可销天分类2,
                    $exportOneData['field39'] = $field39; //7日销售分类,
                    $exportOneData['field40'] = $field40; //昨日销售分类,
                    $exportOneData['field41'] = $field41; //在仓量分类,
                    $exportOneData['field42'] = $field42; //7日内回货量,
                    $exportOneData['field43'] = $field43; //7天回货后可销天,
                    $exportOneData['field44'] = $field44; //商品建议销售等级,
                    $exportOneData['field45'] = $field45; //商品建议销售等级,
                    $exportOneData['field46'] = $field46; //3日内发货,
                    $exportOneData['field47'] = $field47; //3日内回货可销天,
                    $exportOneData['field48'] = $field48; //商品建议销售等级(3日内回货),
                    $exportOneData['field49'] = $field49; //商品建议销售等级(3日内回货),
                    $exportOneData['field50'] = $field50; //7日回货覆盖缺货,

                    $exportData[$oneData['SKU']] = $exportOneData;

                    ff::$app->exportData['dataSyncDateTime'] = $oneData['dataSyncDateTime'];
                    ff::$app->exportData['dataSyncCount']++;

                }
                $row++;

                //生成表格数据

            });

        echo '<pre>';
        var_dump(count($exportData));
        echo '</pre>';
        exit;

        $this->normalBuild($exportData);
    }
}
