<?php
namespace ff\auth;

use ff\database\UserSuppliersModel;
use ff\code\ErrorCode;


class TokenAuthSuppliersController extends TokenAuthController
{
    public function init($uid,$token){
        //select db check user token
        $userModel = new UserSuppliersModel();
        $this->user = $userModel->init($uid, $token);

        if (!$this->user->uid) {
            return ErrorCode::TOKEN_FAILED();
        }

        return null;
    }

}
