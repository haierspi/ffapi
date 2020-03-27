<?php
namespace controllers\v1_0;

use ff\base\Controller;
use models\v1_0\omsUser;

/**
 *
 * @name 用户令牌相关
 *
 */

class UserController extends Controller
{

    /**
     *
     * @name  用户登陆获取TOKEN
     * @method POST
     * @format JSON
     * @param string account yes 登陆账户;自动判断用户名/手机号/邮箱
     * @param string password no 登陆密码
     * @var int code 状态码 (成功 1 ;失败 0;)
     * @var string msg 状态信息
     * @var object userData 用户信息
     * @other 本接口附带登陆COOKIE
     * @example
     * [success]JSON:{"status":1,"data":{"face":"63800205.jpg","name":"asd12","province":"266","city":"267","gender":"1","birthday":"1367401462","username":"admin"}}
     * @author haierspi
     *
     */
    public function actionPWLogin($method = 'POST')
    {
        $account = $this->request->vars['account'] ?? null;
        $password = $this->request->vars['password'];

        if (!$account || !$password) {
            return ['code' => -1010]; //缺少参数
        }

        $account = trim($account);

        //邮箱登陆
        if (preg_match("/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/", $account)) {
            $type = 'email';
        }
        //手机号
        elseif (preg_match("/^1\d{10}$/", $account)) {
            $type = 'mobile';
        }
        // 用户名登陆
        else {
            $type = 'username';
        }

        $omsUserModel = new omsUser();
        list($userData, $verifyPassword) = $omsUserModel->getUserByField($account, $type);

        if (!$userData) {
            return ['code' => -2004]; // '用户不存在或被禁用！';
        }

        if (!$omsUserModel->checkPassword($password, $verifyPassword)) {
            return ['code' => -2005];
        }

        $token = $omsUserModel->getToken($userData['uid']);

        $userData['token'] = $token;

        return SussedCode::SUSSED([
            'token' => $token, 'userData' => $userData
        ]);


    }

    /**
     *
     * @name  用户登陆(密码登陆,验证码登陆)
     * @method GET
     * @format JSON
     * @param string type no 账号类型 1:手机号; 2:邮箱
     * @param string account yes 账号(手机号或邮箱)
     * @param string codetype no 验证码类型  0:密码 (默认) 1:验证码(手机号或邮箱)
     * @param string code yes 密码
     * @var int code 状态码 (成功 1 ;失败 0;)
     * @var string msg 状态信息
     * @var string memberdata
     * @other 本接口附带登陆COOKIE
     * @example
     * [success]JSON:{"status":1,"data":{"face":"63800205.jpg","name":"asd12","province":"266","city":"267","gender":"1","birthday":"1367401462","username":"admin"}}
     * @author haierspi
     *
     */
    public function actionLogin($method = 'GET|POST')
    {
        $Member = new Member;

        $type = (int) $this->request->vars['type'];
        $account = $this->request->vars['account'] ?? null;
        $codetype = (int) $this->request->vars['codetype'];
        $code = $this->request->vars['code'];

        //手机
        if ($codetype == 1) {
            if (!$account || !preg_match("/^1[0-9]{1}\d{9}$/", $account)) {
                return ['code' => -2009];
            }
        }
        //邮箱
        elseif ($codetype == 2) {
            if (!$account || preg_match('/^([a-zA-Z0-9_-])+@[a-zA-Z0-9_-]+(\.[a-zA-Z0-9_-]+)+$/is', $account)) {
                return ['code' => -2010];
            }
        }
        //不存在的类型
        else {
            return ['code' => -2002];
        }

        if (!$code) {
            if ($codetype) {
                return ['code' => -2011]; //验证码不能为空
            } else {
                return ['code' => -2014]; //密码不能为空
            }
        }

        $member = $Member->getByType($account, $type);

        //验证码
        if ($codetype) {

            $member_verify = $Member->getVerifyCodeByType($account, $type);

            if (!$member_verify || TIMESTAMP > $member_verify['expiration']) {
                return ['code' => -2012];
            }

            if ($member_verify['code'] != $code) {
                return ['code' => -2013];
            }

            // if (!$member) {
            //     return ['code' => -2006]; // 该用户不存在
            // }
            // //用户登录
            // else

            if ($member) {
                $token = $Member->getToken($member['uid']);
                $Member->delVerifyCodeByType($account, $type);

            }
            //自动注册 这里已经不允许自动注册
            else {

                $member = $Member->createByType($account, $type);

                $token = $Member->getToken($member['uid']);

                $Member->delVerifyCodeByType($account, $type);

            }
        } else {

            if (!$member) {
                return ['code' => -2006];
            } else {

                if (!$Member->checkPassword($member['uid'], $code)) {
                    return ['code' => -2017];
                }

                $token = $Member->getToken($member['uid']);
            }

        }

        return SussedCode::SUSSED([
            'token' => $token, 'memberdata' => $member
        ]);

    }

    /**
     *
     * @name  发送验证码
     * @method GET
     * @format JSON
     * @param string type no 账号类型 1:手机号; 2:邮箱
     * @param string account yes 账号 (提供本参数限制为获取单挑数据,)
     * @var int code 状态码 (成功 1 ;失败 0;)
     * @var string msg 状态信息
     * @var string memberdata
     * @other 本接口附带登陆COOKIE
     * @example
     * [success]JSON:{"status":1,"data":{"face":"63800205.jpg","name":"asd12","province":"266","city":"267","gender":"1","birthday":"1367401462","username":"admin"}}
     * @author haierspi
     *
     */
    public function actionSendcode($method = 'GET')
    {
        $type = (int) ($this->request->vars['type'] ?? 0);
        $account = $this->request->vars['account'] ?? null;

        //验证手机
        if ($type == 1) {
            if (!$account || !preg_match("/^1[0-9]{1}\d{9}$/", $account)) {
                return ['code' => -2009];
            }
            $verifycodetype = 1;
        }
        //邮箱
        elseif ($type == 2) {
            if (!$account || preg_match('/^([a-zA-Z0-9_-])+@[a-zA-Z0-9_-]+(\.[a-zA-Z0-9_-]+)+$/is', $account)) {
                return ['code' => -2010];
            }
            $verifycodetype = 2;
        }
        //不允许出现code为0
        else {
            return ['code' => -2002];
        }

        $member_verify = DB::fetch_first("SELECT `id`,`type`,`account`,`code`,UNIX_TIMESTAMP(`expiration`) as `expiration`
                            FROM " . DB::table('member_verify') . "
                                WHERE `type`='{$verifycodetype}' AND `account`='$account' ORDER BY `datetime` DESC");

        if ($member_verify && TIMESTAMP <= $member_verify['expiration']) {
            return ['code' => -2066]; //验证码60秒内只能发送一次
        }

        DB::delete('member_verify', "  `type`='$verifycodetype' AND `account`='$account' ");

        $code = StringLib::randstring(6, 'N');

        //先插入
        $verifydata = array();
        $verifydata['id'] = DB::next_autoid('member_verify');
        $verifydata['type'] = $verifycodetype;
        $verifydata['account'] = $account;
        $verifydata['code'] = $code;
        $verifydata['expiration'] = date('Y-m-d H:i:s', TIMESTAMP + 600);
        $verifydata['datetime'] = date('Y-m-d H:i:s', TIMESTAMP);

        DB::insert('member_verify', $verifydata);

        if ($type == 1) {
            //发送短信
            $response = aliyunsms::sendSecurityCode($account, $code);

            return SussedCode::SUSSED([
                'msg' => '验证码已经发送到您的手机上', 'response' => $response
            ]);
        }
        //验证邮箱
        elseif ($type == 2) {
            //邮箱发送邮件
            return SussedCode::SUSSED([
                'msg' => '验证码已经发送到您邮箱中'
            ]);
        }

    }

    /**
     *
     * @name  随机用户登陆 (仅用于测试使用)
     * @method GET
     * @format JSON
     * @var int code 状态码 (成功 1 ;失败 0;)
     * @var string msg 状态信息
     * @var string memberdata
     * @other 本接口附带登陆COOKIE
     * @example
     * [success]JSON:{"status":1,"data":{"face":"63800205.jpg","name":"asd12","province":"266","city":"267","gender":"1","birthday":"1367401462","username":"admin"}}
     * @author haierspi
     *
     */
    public function actionGuestLogin($method = 'POST|GET')
    {
        $Member = new Member;
        $memberdata = $Member->createGuest();
        $token = $Member->getToken($memberdata['uid']);

        return SussedCode::SUSSED([
            'token' => $token, 'memberdata' => $memberdata
        ]);
    }

    /**
     *
     * @name  用户注销
     * @method GET
     * @format JSON
     * @param string newsId yes 账号 (提供本参数限制为获取单挑数据,)
     * @param string type yes 返回类型新闻
     * @param string page yes 账号
     * @param string pageNum yes 密码
     * @var int status 状态码 (成功 1 ;失败 0;)
     * @var string msg 状态信息
     * @var string memberdata
     * @other 本接口附带登陆COOKIE
     * @example
     * [success]JSON:{"status":1,"data":{"face":"63800205.jpg","name":"asd12","province":"266","city":"267","gender":"1","birthday":"1367401462","username":"admin"}}
     * @author haierspi
     *
     */
    public function actionLogout($method = 'GET', $auth = 'Token')
    {

        $Member = new Member;
        $user = new user;

        $token = $Member->delToken($user->uid);

        return SussedCode::SUSSED([
            'token' => $token, 'memberdata' => $memberdata, 'wxmemberdata' => $wxmemberdata
        ]);

    }

    /**
     *
     * @name  微信 - CODE注册登陆
     * @method POST
     * @format JSON
     * @param string code yes 微信授权code
     * @param string codetype no CODE类型 : mediaplatform 公众号 (默认)  miniprogram 小程序
     * @var int code 状态码 (成功 1 ;失败 0;)
     * @var object wxerrorinfo 当微信授权错误的时候返回
     * @other 本接口附带登陆COOKIE
     * @example
     * [success]JSON:{"code":1,"msg":"\u767b\u9646\u6210\u529f","token":"ADGC3e6GoLODjYXX2cM6BwNWDgNXUwIABjh8AXtgfndJVxURGRFfWSZBZ0YjNQ==953b1f5da1","memberdata":{"uid":"9","email":null,"mobile":null,"nickname":"\u4e0d\u5403\u6d77\u5e26","avatar":"http:\/\/thirdwx.qlogo.cn\/mmopen\/vi_32\/ajNVdqHZLLDohmGrPLLicr9MpZSDFHia4lzKHGXLYO4qosxoicHEJb47H1NcqfzPKdRmUmhJqKQSTWpQDKgWFzqSw\/132","level":"0","datetime":"2018-05-27 15:29:02","vip":"0","shopmanager":"0","manager":"0","boss":"0","showshopid":"0"},"wxmemberdata":{"uid":"9","wx_openid":"oyImFs34bb230gI12nxgNw1N77gA","wx_nickname":"\u4e0d\u5403\u6d77\u5e26","wx_sex":"1","wx_language":"zh_CN","wx_city":"\u6d66\u4e1c\u65b0\u533a","wx_province":"\u4e0a\u6d77","wx_country":"\u4e2d\u56fd","wx_headimgurl":"http:\/\/thirdwx.qlogo.cn\/mmopen\/vi_32\/ajNVdqHZLLDohmGrPLLicr9MpZSDFHia4lzKHGXLYO4qosxoicHEJb47H1NcqfzPKdRmUmhJqKQSTWpQDKgWFzqSw\/132"},"request_params":{"shopid":"1","fromuid":"1","code":"021PXtSL1QaWT5134NSL1T1KSL1PXtS5"},"request_dateline":1527486680.968086,"response_dateline":1527486681.22907}
     * @author haierspi
     *
     */
    public function actionWxLogin($method = 'POST')
    {

        $code = $this->request->vars['code'] ?? null;
        // $fromuid = (int) $this->request->vars['fromuid'];
        // $shopid = (int) $this->request->vars['shopid'];

        $codetype = $this->request->vars['codetype'] ?? null;
        $encrypteddata = $this->request->vars['encrypteddata'] ?? null;
        $iv = $this->request->vars['iv'] ?? null;

        if (empty($code)) {
            return ['code' => -2001];
        }

        $WeixinStd = new Weixin;

        if ($codetype == 'miniprogram') {
            $WeixinStd->miniprogram = true;

            $wxredata = $WeixinStd->get_userinfo_miniprogram($code, $encrypteddata, $iv);

        } else {

            // 第一步: 获取token 判断错误
            $tokendata_json = $WeixinStd->get_access_token($code);
            $tokendata = json_decode($tokendata_json, true);

            if ($tokendata['errcode']) {
                return ['code' => -2034, 'wxerrorinfo' => $tokendata]; //微信授权失败
            }

            // 第二步: 获取$OPENID,$ACCESS_TOKEN 判断报错
            $wxredata_json = $WeixinStd->get_openid_userinfo($tokendata['openid'], $tokendata['access_token']);

            $wxredata = json_decode($wxredata_json, true);

            if ($wxredata['errcode']) {
                return ['code' => -2034, 'wxerrorinfo' => $wxredata]; //微信授权失败
            }

        }

        unset($wxredata['privilege']);
        $OPENID = $wxredata['openid'];

        $memberdata = $wxmemberdata = array();

        $wxmemberdata = DB::fetch_first("SELECT * FROM " . DB::table('member_weixin') . " WHERE `wx_openid`='{$OPENID}' LIMIT 1");

        $Member = new Member;
        //找到微信 信息
        if ($wxmemberdata) {

            //补充昵称
            if (!$wxmemberdata['wx_nickname']) {
                $Member->updateReplenish($wxmemberdata['uid'], $wxredata['nickname'], $wxredata['headimgurl']);
            }
            if (!$wxmemberdata['wx_nickname'] || $wxmemberdata['wx_nickname'] != $wxredata['nickname']) {
                $wxmemberdata = $WeixinStd->update_wxmember($wxredata['openid'], $wxredata);
            }
            if (!$wxunion = $WeixinStd->get_wxunion($wxredata['unionid'])) {
                $WeixinStd->creat_wxunion($wxmemberdata['uid'], $wxredata['openid'], $wxredata['unionid']);
            }
            //更新openid 到 个人表
            $WeixinStd->update_wxunion($uid, $wxredata['openid']);

            $memberdata = $Member->getByType($wxmemberdata['uid'], 0);
            $token = $Member->getToken($wxmemberdata['uid']);
        }
        //未找到微信信息
        else {

            //如果没有 开放平台id
            if (!$wxunion = $WeixinStd->get_wxunion($wxredata['unionid'])) {
                $memberdata = $Member->createByNickname($wxredata['nickname'], $wxredata['headimgurl']);
                $WeixinStd->creat_wxunion($memberdata['uid'], $wxredata['openid'], $wxredata['unionid']);
                $uid = $memberdata['uid'];
            } else {
                $uid = $wxunion['uid'];
                //更新openid 到 个人表
                $WeixinStd->update_wxunion($uid, $wxredata['openid']);
            }

            //创建微信信息
            $wxmemberdata = $WeixinStd->creat_wxmember($uid, $wxredata);

            //初始化关系
            if (!$Member->getMemberRelationship($wxmemberdata['uid'])) {
                $insertdata = array();
                $insertdata['uid'] = $wxmemberdata['uid'];
                $insertdata['wx_openid'] = $wxredata['openid'];
                $insertdata['datetime'] = (string) date('Y-m-d H:i:s', TIMESTAMP);
                $insertdata['level'] = 1;
                DB::insert('member_relationship', $insertdata);
            }

            $token = $Member->getToken($memberdata['uid']);
        }

        return SussedCode::SUSSED([
            'token' => $token, 'memberdata' => $memberdata, 'wxmemberdata' => $wxmemberdata
        ]);
    }

    /**
     *
     * @name  微信-获取授权跳转地址
     * @method GET
     * @format JSON
     * @param string loginurl yes 微信调用的授权返回地址(code登陆地址)
     * @var int status 状态码 (1: 成功; 负数:失败; )
     * @var string wxauthurl  微信授权跳转地址
     * @example
     * [success]JSON:{"code":1,"wxcallurl":"https:\/\/open.weixin.qq.com\/connect\/oauth2\/authorize?appid=wxa46bf719afc7da09&redirect_uri=http%3A%2F%2Fwww.whaley-vr.com%2Fwx%2F%3Fgo%3Dhttp%253A%252F%252F127.0.0.1%253A1000%252F&response_type=code&scope=snsapi_userinfo&state=STATE#wechat_redirect","request_params":{"loginurl":"http:\/\/127.0.0.1:1000\/"},"request_dateline":1527486800.136032,"response_dateline":1527486800.153398}
     * @author haierspi
     *
     */
    public function actionWxcall($method = 'GET')
    {
        $loginurl = $this->request->vars['loginurl'] ?? null;

        if (empty($loginurl)) {
            return ['code' => -2001];
        }
        $WeixinStd = new Weixin;

        $wxcallurl = $WeixinStd->buil_callurl($loginurl);


        return SussedCode::SUSSED([
            'wxcallurl' => $wxcallurl
        ]);

    }

}
