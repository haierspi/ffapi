<?php
namespace ff\auth;

use ff;
use ff\database\PrivilegesBase;

class PrivilegesTokenAuthController extends TokenAuthController
{
    private $privilegesModel;
    public function beforeAction()
    {
        $verifyResult = parent::beforeAction();
        if (!is_null($verifyResult)) {
            return $verifyResult;
        }

        $this->privilegesModel = ff::createObject('ff\database\PrivilegesBase', [$this->user->uid, $this]);

        $verifyResult = $this->privilegesModel->checkUserPermissionAccessController();

        if (!is_null($verifyResult)) {
            return $verifyResult;
        }

    }

    public function checkAccess($checkKey)
    {

        return $this->privilegesModel->checkUserPermissionAccess($checkKey);
    }

    public function getContent()
    {
        return $this->privilegesModel->getUserContent();
    }

    public function isAllContent()
    {
        return $this->privilegesModel->getUserContentIsAll();
    }

    public function checkContent($checkKey)
    {
        return $this->privilegesModel->checkUserContentAccess($checkKey);
    }

}
