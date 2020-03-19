<?php
namespace ff\database;

class UserSuppliersModel extends Model
{
    public $table = 'suppliers_new';
    private static $inited = false;
    private static $propertys = [];

    public function __construct()
    {}

    public function init($uid = null, $token = null)
    {
        $userdata = $this->where('id', $uid)->limit(1)->first();

        if (isset($userdata->attributes['id'])) {
            $userdata->attributes['uid'] = $userdata->attributes['id'];
        }

        if (isset($userdata->attributes['token']) && $userdata->attributes['token'] == $token) {
            unset($userdata->attributes['password']);
            self::$propertys = $userdata->attributes;
        }
        if (isset($userdata->attributes['id'])) {
            $idList = DB::table('suppliers_new')->where('full_name',$userdata['full_name'])->pluck('id')->toArray();
//            $idList = $this->where('full_name',$userdata['full_name'])->pluck('id');

            self::$propertys['suppliersIdList'] = $idList;
        }
        self::$inited = true;

        return $userdata;

    }

    public function asArray()
    {
        return self::$propertys;
    }

    public function __get($name)
    {
        if (!self::$inited) {
            $this->init();
        }
        return self::$propertys[$name] ?? null;

    }
}
