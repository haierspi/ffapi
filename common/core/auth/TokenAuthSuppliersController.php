<?php
namespace ff\auth;

use ff\database\UserSuppliersModel;


class TokenAuthSuppliersController extends TokenAuthController
{
    public function init($uid,$token){
        //select db check user token
        $userModel = new UserSuppliersModel();
        $this->user = $userModel->init($uid, $token);

        if (!$this->user->uid) {
            return ['code' => -1006];
        }

        return null;
    }

}
