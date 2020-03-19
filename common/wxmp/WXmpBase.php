<?php

namespace common\wxmp;

use ff;
use ff\caching\rediscache;
use ff\database\db;
use ff\helpers\StringLib;

class WXmpBase
{
    //服务端TOKEN授权地址
    const WX_TOKEN_URL = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={APPID}&secret={APPSECRET}';
    //微信jsapi_ticket
    const WX_JSAPI_TICKE_TURL = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token={TOKEN}&type=jsapi';
    //扫码授权地址
    const WX_QRCODE_URL = 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token={TOKEN}';

    //扫码二维码图片地址
    const WX_QRCODEIMAGE_URL = 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket={TICKET}';
    private $APPID;
    private $APPSECRET;
    private $TOKEN;
    private $TICKET;
    private $JSAPI_TICKET;

    public function __construct()
    {
        $this->APPID = ff::$config['weixin']['APPID'];
        $this->APPSECRET = ff::$config['weixin']['APPSECRET'];
    }
    //获取微信token
    public function get_access_token()
    {
        if (($Token = rediscache::get('WXMP_SERVICE_TOKEN')) === false) {

            $resultConent = StringLib::geturlconent($this->urlparse(SELF::WX_TOKEN_URL));
            $resultData = json_decode($resultConent, 1);
            if (isset($resultData['errcode'])) {
            } else {
                $Token = $resultData['access_token'];
                rediscache::set('WXMP_SERVICE_TOKEN', $Token, $resultData['expires_in']);
            }

            
        }
        $this->TOKEN = $Token;
        return $Token;
    }
    //获取微信jsapi_ticket
    public function get_jsapi_ticket()
    {
        if (!$this->TOKEN) {
            $this->get_access_token();
        }
        if (($jsapi_ticket = rediscache::get('WXMP_SERVICE_JSAPI_TICKET')) === false) {

            $resultConent = StringLib::geturlconent($this->urlparse(SELF::WX_JSAPI_TICKE_TURL));
            $resultData = json_decode($resultConent, 1);

            if ($resultData['errcode'] != 0) {
            } else {
                $jsapi_ticket = $resultData['ticket'];

                
                rediscache::set('WXMP_SERVICE_JSAPI_TICKET', $jsapi_ticket, $resultData['expires_in']);
            }
        }
        $this->JSAPI_TICKET = $jsapi_ticket;
        return $jsapi_ticket;
    }

    public function get_jsapi_signpackage($durl)
    {
        //接收到前端的转义url转义回来

        $jsapiTicket = $this->get_jsapi_ticket();
        $timestamp = TIMESTAMP;
        $nonceStr = $this->createNonceStr();
        // 这里参数的顺序要按照 key 值 ASCII 码升序排序
        $string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$durl";
        


        $signature = sha1($string);

        $signPackage = [
            "appId" => (string) $this->APPID,
            "nonceStr" => (string) $nonceStr,
            "timestamp" => (string) $timestamp,
            "url" => (string) $durl,
            "signature" => (string) $signature,
        ];
        return $signPackage;
    }

    private function createNonceStr($length = 16)
    {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    // 解析URL中的参数
    public function urlparse($URL)
    {
        $URL = preg_replace_callback(
            '/{([a-zA-Z0-9]+)}/is',
            function ($matches) {
                return $this->{$matches[1]};
            },
            $URL
        );
        return $URL;
    }

    //获取扫码Ticket
    public function get_qrcode($value, $isstr = true, $lasting = false, $expire = 604800)
    {
        if (!$this->TOKEN) {
            $this->get_access_token();
        }
        $param_action_name = ['QR'];
        if ($lasting) {
            $param_action_name[] = 'LIMIT';
        }
        if ($isstr) {
            $param_action_name[] = 'STR';
            $scenefield = 'scene_str';
            $value = (STRING) $value;
        } else {
            $scenefield = 'scene_id';
            $value = (INT) $value;
        }
        $param_action_name[] = 'SCENE';

        $param = [
            "expire_seconds" => $expire,
            "action_name" => join('_', $param_action_name),
            "action_info" => [
                "scene" => [$scenefield => $value],
            ],
        ];

        $resultConent = StringLib::geturlconent($this->urlparse(SELF::WX_QRCODE_URL), $param, [], $headers, 0, '', 15, 'JSON');
        $resultData = json_decode($resultConent, 1);
        if ($resultData['ticket']) {
            $this->TICKET = $resultData['ticket'];
            $resultData['lasting'] = $lasting;
        }
        return $resultData;
    }

    //获取扫码图片
    public function get_qrcode_image($ticket = null)
    {
        if (!is_null($ticket)) {
            $this->TICKET = $ticket;
        }
        return StringLib::geturlconent($this->urlparse(SELF::WX_QRCODEIMAGE_URL));
    }
}
