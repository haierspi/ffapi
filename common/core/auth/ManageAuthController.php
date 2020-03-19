<?php
namespace ff\auth;

use ff\auth\TokenAuthController;
use ff\base\Controller;
use ff\helpers\TokenParse;
use ff\network\Request;
use models\v1_0\Shop;
use ff\base\user;

class ManageAuthController extends TokenAuthController
{
    public function beforeAction()
    {
        $Shop = new Shop;
        $user = new user;
        if (!$boss = $Shop->getShopBoss($user->uid)) {
            return ['code' => -2030]; //权限错误或店铺信息错误
        }
        if (!$ushop = $Shop->getUidShop($user->uid, $shopid)) {
            return ['code' => -2030]; //权限错误或店铺信息错误
        }
        parent::beforeAction();
    }

}
