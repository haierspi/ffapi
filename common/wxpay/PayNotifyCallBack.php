<?php
namespace common\wxpay;

use ff;
use ff\database\db;
use common\wxpay\lib\WxPayApi;
use common\wxpay\lib\WxPayNotify;
use common\wxpay\lib\WxPayOrderQuery;
use  models\v1_0\Paylog;
use models\v1_0\Order;



class PayNotifyCallBack extends WxPayNotify
{
	//查询订单
	public function Queryorder($transaction_id)
	{

		$input = new WxPayOrderQuery();
		$input->SetTransaction_id($transaction_id);
        $result = WxPayApi::orderQuery($input);

		//Log::DEBUG("query:" . json_encode($result));
		if(array_key_exists("return_code", $result)
			&& array_key_exists("result_code", $result)
			&& $result["return_code"] == "SUCCESS"
			&& $result["result_code"] == "SUCCESS")
		{
			return true;
		}
		return false;
	}
	
	//重写回调处理函数
	public function NotifyProcess($data, &$msg)
	{
		//Log::DEBUG("call back:" . json_encode($data));
		$notfiyOutput = array();
		
		if(!array_key_exists("transaction_id", $data)){
			$msg = "输入参数不正确";
			return false;
		}
		//查询订单，判断订单真实性
		if(!$this->Queryorder($data["transaction_id"])){
			$msg = "订单查询失败";
			return false;
        }
        $PaylogStd = new Paylog;
        
        $paylogOne = $PaylogStd->getPaybySN($data['out_trade_no']);
        
        if (!$paylogOne['status']) {
            $PaylogStd->updatePayNotify($data,$paylogOne['payid']);
            $PaylogStd->setPayed($paylogOne['payid']);

            $typeids = explode(',', $paylogOne['typeids']);

            if ( $paylogOne['type'] == 'order') {
                $orderStd = new Order;
                $orderStd->setPayed($paylogOne,$typeids,'wxpay');
            }

        }

		return true;
	}
}
