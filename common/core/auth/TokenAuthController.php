<?php
namespace ff\auth;

use ff\base\Application;
use ff\base\Controller;
use ff\database\UserModel;
use ff\helpers\TokenParse;
use ff\network\Request;

class TokenAuthController extends Controller
{
    protected $user = [];
    public function beforeAction()
    {
        $verifyResult = $this->auth();
        if (!is_null($verifyResult)) {
            return $verifyResult;
        }
        parent::beforeAction();
    }

    private function auth($callrunController = null, $vars = null)
    {

        if (is_null($vars)) {
            $token = $this->request->vars['token'] ?? ($this->request->headerVars['token'] ?? null);
        }

        $token = $token ?? null;

        if (!isset($token)) {
            return ['code' => -1005];
        }
        list($uid, $nickname, $expiration) = TokenParse::get($token);

        if (empty($uid)) {
            return ['code' => -1006];
        }

        if (!$expiration || $expiration < TIMESTAMP) {
            return ['code' => -1007];
        }

        return $this->init($uid, $token);
    }

    public function init($uid, $token)
    {
        //select db check user token
        $this->user = new userModel();
        $this->user->init($uid, $token);

        if (is_null($this->user) || !$this->user->uid) {
            return ['code' => -1006];
        }

        return null;
    }

    public function checkAccess($x, $y, $z)
    {
        return "$x,$y,$z";
    }
}
