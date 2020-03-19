<?php
namespace models\v1_0;

use ff\database\db;
use ff\helpers\StringLib;
use ff\helpers\TokenParse;
use models\v1_0\cmpMember;

class Member
{
    const TYPE_UID_FIELD = 'uid';
    const TYPE_UID = 0; //手机号码
    const TYPE_MOBILE_FIELD = 'mobile';
    const TYPE_MOBILE = 1; //手机号码
    const TYPE_EMAIL_FIELD = 'email';
    const TYPE_EMAIL = 2; //邮箱地址
    const TYPE_MOBILE_BIND = 3; //手机号
    const TYPE_EMAIL_BIND = 4; //邮箱

    const TOKEN_EXPIRATION = 31536000; //过期时间

    //根据ID,mobile,email 获取 用户信息
    public function getByType($account = '', $type = 0)
    {
        $TypeFields = [
            SELF::TYPE_UID => SELF::TYPE_UID_FIELD,
            SELF::TYPE_MOBILE => SELF::TYPE_MOBILE_FIELD,
            SELF::TYPE_EMAIL => SELF::TYPE_MOBILE_FIELD,
        ];

        $field = isset($TypeFields[$type]) ? $TypeFields[$type] : null;

        return DB::fetch_first("SELECT `uid`,`email`,`mobile`,`nickname`,`avatar`,`level`,`datetime` FROM " . DB::table('{{%member}}') . " WHERE `$field`='$account' LIMIT 1");
    }

    public function nicknameFilter($nickname = '', $uid = 0)
    {
        if ($nickname) {
            $nickname = trim($nickname);
            $nickname = preg_replace('/[^0-9a-zA-Z-_\x{4e00}-\x{9fff}]+/u', '', $nickname);
        }

        $i = 0;
        if (empty($nickname)) {
            $nickname = 'User';
            $i = 1;
        }
        do {
            if ($i > 0) {
                $nickname = $nickname . StringLib::randstring($i, 'N');
            }
            $i++;

            if ($uid) {
                $uidsql = " AND `uid` <> '{$uid}'";
            } else {
                $uidsql = '';
            }
            $isexist = DB::fetch_first("SELECT uid FROM " . DB::table('{{%member}}') . " WHERE `nickname`='$nickname'  {$uidsql}  LIMIT 1");
        } while ($isexist);
        return $nickname;
    }

    //根据UID获取全部用户信息
    public function getAllByUID($uid)
    {
        return DB::fetch_first("SELECT * FROM " . DB::table('{{%member}}') . " WHERE `uid`='{$uid}' LIMIT 1");
    }

    public function getVerifyCodeByType($account = '', $type = 0)
    {

        $Types = [SELF::TYPE_MOBILE, SELF::TYPE_EMAIL, SELF::TYPE_MOBILE_BIND, SELF::TYPE_EMAIL_BIND];

        $type = isset($Types[$type]) ? $type : null;

        return DB::fetch_first("SELECT `id`,`type`,`account`,`code`,UNIX_TIMESTAMP(`expiration`) as `expiration` FROM " . DB::table('member_verify') . " WHERE `type`='$type' AND `account`='$account' ORDER BY `datetime` DESC LIMIT 1");
    }

    //根据mobile,email信息创建账户
    public function createByType($account = '', $type = 0, $nickname = null)
    {

        $TypeFields = [SELF::TYPE_MOBILE => SELF::TYPE_MOBILE_FIELD, SELF::TYPE_EMAIL => SELF::TYPE_MOBILE_FIELD];
        $field = isset($TypeFields[$type]) ? $TypeFields[$type] : null;

        if (is_null($nickname)) {
            if (SELF::TYPE_MOBILE == $type) {
                $nickname = preg_replace('/([0-9]{3})([0-9]{4})([0-9]{4})/is', '\1****\3', $account);
            } elseif (SELF::TYPE_EMAIL == $type) {
                list($nickname) = explode('@', $account);
            }
        }

        $mobile = 'mobile' == $field ? $account : null;
        $email = 'email' == $field ? $account : null;

        //先插入
        $memberdata = array();
        $memberdata['uid'] = (string) DB::next_autoid('{{%member}}');
        if (SELF::TYPE_MOBILE == $type) {
            $memberdata['mobile'] = $mobile;
        } elseif (SELF::TYPE_EMAIL == $type) {
            $memberdata['email'] = $email;
        }

        $memberdata['nickname'] = $this->nicknameFilter($nickname);
        $memberdata['pwcode'] = (string) StringLib::randstring(20, 'UMN');
        $memberdata['avatar'] = '';
        $memberdata['datetime'] = (string) date('Y-m-d H:i:s', TIMESTAMP);
        DB::insert('{{%member}}', $memberdata);
        DB::insert('member_count', ['uid' => $memberdata['uid']]);

        return $this->getByType($memberdata['uid'], 0);
    }
    //微信根据昵称创建用户
    public function createByNickname($nickname, $avatar, $bingshopid = '0', $fromuid = '0')
    {
        $memberdata = array();
        $memberdata['uid'] = (string) DB::next_autoid('{{%member}}');
        $memberdata['nickname'] = $this->nicknameFilter($nickname);
        $memberdata['pwcode'] = (string) StringLib::randstring(20, 'UMN');
        $memberdata['avatar'] = $avatar;
        $memberdata['datetime'] = (string) date('Y-m-d H:i:s', TIMESTAMP);

        $memberdata['bingshopid'] = $bingshopid;
        $memberdata['fromuid'] = $fromuid;

        DB::insert('{{%member}}', $memberdata);
        DB::insert('member_count', ['uid' => $memberdata['uid']]);

        return $this->getByType($memberdata['uid'], 0);
    }

    //微信创建UID
    public function createByNodata($fromuid = 0, $bingshopid = 0)
    {
        $memberdata = array();
        $memberdata['uid'] = (string) DB::next_autoid('{{%member}}');
        $memberdata['fromuid'] = $fromuid;
        $memberdata['bingshopid'] = $bingshopid;
        $memberdata['pwcode'] = (string) StringLib::randstring(20, 'UMN');
        $memberdata['datetime'] = (string) date('Y-m-d H:i:s', TIMESTAMP);
        DB::insert('{{%member}}', $memberdata);
        DB::insert('member_count', ['uid' => $memberdata['uid']]);

        return $this->getByType($memberdata['uid'], 0);
    }

    //用户补充
    public function updateReplenish($uid, $nickname, $avatar = '')
    {
        $memberdata = [];
        $memberdata['nickname'] = $this->nicknameFilter($nickname, $uid);
        $memberdata['avatar'] = $avatar;

        DB::update('{{%member}}', $memberdata, "`uid`='{$uid}'");

    }

    //创建匿名账户
    public function createGuest()
    {

        $memberdata = array();
        $memberdata['uid'] = (string) DB::next_autoid('{{%member}}');
        $memberdata['nickname'] = $this->nicknameFilter();
        $memberdata['pwcode'] = (string) StringLib::randstring(20, 'UMN');
        $memberdata['avatar'] = '';
        $memberdata['datetime'] = (string) date('Y-m-d H:i:s', TIMESTAMP);

        $status =DB::insert('{{%member}}', $memberdata);

        if( $status ){
            DB::insert('{{%member_count}}', ['uid' => $memberdata['uid']]);
        }
        return $this->getByType($memberdata['uid'], 0);
    }

    //删除验证码
    public function delVerifyCodeByType($account = '', $type = 0)
    {

        $Types = [SELF::TYPE_MOBILE, SELF::TYPE_EMAIL, SELF::TYPE_MOBILE_BIND, SELF::TYPE_EMAIL_BIND];

        $type = isset($Types[$type]) ? $type : current($Types);

        return DB::delete('{{%member_verify}}', "  `type`='$type' AND `account`='$account'");
    }

    //获取并更新token
    public function getToken($uid)
    {

        $memberdata = $this->getAllByUID($uid);

        $token = TokenParse::set($memberdata['uid'], $memberdata['nickname'], TIMESTAMP + SELF::TOKEN_EXPIRATION, $memberdata['pwcode']);

        DB::update('{{%member}}', ['token' => $token], "`uid`='{$uid}'");

        return $token;

    }

    //获取用户各类积分
    public function getCount($uid, $field = null)
    {
        $member_count = DB::fetch_first("SELECT * FROM " . DB::table('member_count') . " WHERE `uid`='$uid' LIMIT 1");

        if (!$member_count) {
            DB::insert('{{%member_count}}', ['uid' => $uid]);
        }
        if ($field) {
            return (float) $member_count[$field];
        }
        unset($member_count['uid']);
        foreach($member_count as $k => $v){
            $member_count[$k] =  (float) $member_count[$k];
        }

        return $member_count;
    }

    //用户积分变动
    public function updateCount($uid, $nickname, $field, $changevalue, $changetype = '', $changemessage = '')
    {

        $fieldcount = $this->getCount($uid, $field);
        if ($fieldcount + $changevalue < 0) {
            return false;
        }

        $updatecount = DB::updateself('{{%member_count}}', [$field => $changevalue], " `uid`='$uid'");

        if ($updatecount) {
            $countlog = array();
            $countlog['uid'] = $uid;
            $countlog['nickname'] = $nickname;
            $countlog['type'] = $field;
            $countlog['beforecredits'] = $fieldcount;
            $countlog['credits'] = $changevalue;
            $countlog['aftercredits'] = $fieldcount + $changevalue;
            $countlog['changetype'] = $changetype;
            $countlog['changemessage'] = $changemessage;
            $countlog['datetime'] = date('Y-m-d H:i:s', TIMESTAMP);
            DB::insert('{{%member_countlog}}', $countlog);
        }

        return $updatecount;
    }

    //获取用户地址列表
    public function getAddress($uid, $id = 0)
    {
        $sql = $id ? " AND `id`='$id' " : "";
        return DB::fetch_all("SELECT * FROM " . DB::table('{{%member_address}}') . " WHERE `uid`='$uid'" . $sql . ' ORDER BY `default` DESC, `datetime` DESC');
    }

    //获取用户地址列表
    public function getDefaultAddress($uid)
    {
        return DB::fetch_first("SELECT * FROM " . DB::table('{{%member_address}}') . " WHERE `uid`='$uid' AND `default`='1' ");
    }

    //修改用户地址
    public function updateAddress($data, $id = 0)
    {
        if ($data['default']) {
            DB::update('member_address}}', ['default' => 0], "`uid`='{$data['uid']}'");
        }
        if (!$this->getDefaultAddress($data['uid'])) {
            $data['default'] = 1;
        }

        if ($id) {
            $sql = " AND `uid`='{$data['uid']}' ";
            return DB::update('{{%member_address}}', $data, "`id`='{$id}'" . $sql);
        } else {
            return DB::insert('{{%member_address}}', $data);
        }

    }

    //修改用户地址
    public function delAddress($id, $uid = 0)
    {

        $sql = $uid ? " AND `uid`='$uid' " : "";
        return DB::delete('{{%member_address}}', "`id`='{$id}'" . $sql);
    }

    //检查密码是否一致
    public function checkPassword($uid, $password)
    {

        $memberdata = $this->getAllByUID($uid);
        return md5($password) == $memberdata['pwcode'];

    }

    //删除token
    public function delToken($uid)
    {

        return DB::delete('{{%member}}', "`uid`='{$uid}'");

    }

    //查询提交昵称是否已存在
    public function getNickNameExist($uid, $nickname)
    {
        return DB::fetch_first("SELECT * FROM " . DB::table('{{%member}}') . " WHERE `nickname`='{$nickname}' AND `uid`<>'$uid' ");
    }

    //修改用户昵称
    public function setNickName($uid, $nickname)
    {
        $data['nickname'] = $nickname;
        return DB::update('{{%member}}', $data, " `uid`='$uid'");
    }


    //查询提交昵称是否已存在
    public function getName($uid)
    {
        $memberdata = $this->getAllByUID($uid);
        return  $memberdata['name'];
    }

    //修改用户昵称
    public function setName($uid, $name)
    {
        return DB::update('{{%member}}', ['name'=>$name], " `uid`='$uid'");
    }
}
