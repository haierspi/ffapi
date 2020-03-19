<?php

namespace common;

use ff;
use Aliyun\Api\Sms\Request\V20170525\QuerySendDetailsRequest;
use Aliyun\Api\Sms\Request\V20170525\SendSmsRequest;
use Aliyun\Core\Config;
use Aliyun\Core\DefaultAcsClient;
use Aliyun\Core\Profile\DefaultProfile;

Config::load();

class aliyunsms
{
    static $config = [];
    static $acsClient = null;

    /**
     * 取得AcsClient
     *
     * @return DefaultAcsClient
     */

    public static function init()
    {
        if(empty(self::$config)){
            self::$config = ff::$config['aliyunsms'];
        }
        
    }
    public static function getAcsClient()
    {
        //产品名称:云通信流量服务API产品,开发者无需替换
        $product = "Dysmsapi";

        //产品域名,开发者无需替换
        $domain = "dysmsapi.aliyuncs.com";
        // 暂时不支持多Region
        $region = "cn-hangzhou";

        // 服务结点
        $endPointName = "cn-hangzhou";

        // TODO 此处需要替换成开发者自己的AK (https://ak-console.aliyun.com/)
        $accessKeyId = self::$config['accessKeyId']; // AccessKeyId

        $accessKeySecret = self::$config['accessKeySecret']; // AccessKeySecret

        if (static::$acsClient == null) {

            //初始化acsClient,暂不支持region化
            $profile = DefaultProfile::getProfile($region, $accessKeyId, $accessKeySecret);

            // 增加服务结点
            DefaultProfile::addEndpoint($endPointName, $region, $product, $domain);

            // 初始化AcsClient用于发起请求
            static::$acsClient = new DefaultAcsClient($profile);
        }
        return static::$acsClient;
    }

    /**
     * 发送短信
     * @return stdClass
     */
    public static function sendSecurityCode($phone, $code)
    {
        self::init();

        // 初始化SendSmsRequest实例用于设置发送短信的参数
        $request = new SendSmsRequest();

        // 必填，设置短信接收号码
        $request->setPhoneNumbers($phone);

        // 必填，设置签名名称，应严格按"签名名称"填写，请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/sign
        $request->setSignName(self::$config['signName']);

        // 必填，设置模板CODE，应严格按"模板CODE"填写, 请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/template
        $request->setTemplateCode(self::$config['templateCode']);

        // 可选，设置模板参数, 假如模板中存在变量需要替换则为必填项
        $request->setTemplateParam(json_encode(array(
            "code" => $code,
        )));

        // 可选，设置流水号
        //$request->setOutId("yourOutId");

        // 选填，上行短信扩展码（扩展码字段控制在7位或以下，无特殊需求用户请忽略此字段）
        //$request->setSmsUpExtendCode("1234567");

        // 发起访问请求
        $acsResponse = static::getAcsClient()->getAcsResponse($request);

        return $acsResponse;

    }

    /**
     * 短信发送记录查询
     * @return stdClass
     */
    public static function querySendDetails($phone, $date, $page = 1, $pagesize = 10)
    {

        // 初始化QuerySendDetailsRequest实例用于设置短信查询的参数
        $request = new QuerySendDetailsRequest();

        // 必填，短信接收号码
        $request->setPhoneNumber($phone);

        // 必填，短信发送日期，格式Ymd，支持近30天记录查询
        $request->setSendDate($date);

        // 必填，分页大小
        $request->setPageSize($pagesize);

        // 必填，当前页码
        $request->setCurrentPage($page);

        // 选填，短信发送流水号
        //$request->setBizId("yourBizId");

        // 发起访问请求
        $acsResponse = static::getAcsClient()->getAcsResponse($request);

        return $acsResponse;
    }
}

/*

for example :

$response = aliyunsms::sendSecurityCode('18616808797','100');

 */
