<?php
namespace ff\database;

class PrivilegesBase
{
    public $uid;
    protected $rootPermission;
    protected $rootContent;
    protected $userPermission;
    protected $userContent;
    protected $userContentIsAll = true;

    protected $allPermission;
    private $controllerPermission;
    private $keyPermission;

    private $controller;

    public function __construct($uid, $controller = null)
    {
        $this->uid = $uid;
        $this->controller = $controller;
        $privilegesMap = require SYSTEM_ROOT_PATH . '/data/privileges.php';
        ksort($privilegesMap['permission']);
        $this->rootPermission = $privilegesMap['permission'];

        $this->rootContent = $privilegesMap['content'];
        $this->handingPermission();
        $this->initUserControl();
    }

    //处理权限
    public function handingPermission()
    {

        foreach ($this->rootPermission as $key => $title) {
            preg_match('/([a-z0-9\/]+)?(\{([a-z0-9_\\\\]+)\h+([a-z]+)\})?/is', $key, $match);

            list(, $premKey, $controllerFull, $controller, $method) = $match;

            $premKey = strtolower($premKey);
            $controller = strtolower($controller);
            $method = strtolower($method);

            $controllerMethod = $premKey . ($controllerFull ? ('{' . $controller . ' ' . $method . '}') : '');
            $id = md5($controllerMethod);

            $oneData = [
                'id' => $id,
                'title' => $title,
                'premKey' => $premKey,
                'controller' => $controller,
                'method' => $method,
                'controllerMethod' => $controllerMethod,
            ];

            $this->allPermission[$id] = $oneData;

            if ($premKey) {
                $this->keyPermission[$premKey] = $oneData;
            }
        }

    }
    //处理权限
    public function handingContent()
    {

    }

    //初始化用户权限
    protected function initUserControl()
    {
        list(
            $this->userPermission,
            $this->userContent
        ) = $this->getUserControl($this->uid);
    }

    //获取用户控制信息信息
    protected function getUserControl($uid)
    {
        $contentPermission = $permission = [];
        $privilegesUser = $this->getUser($uid);

        if ($privilegesUser['is_root']) {

            foreach ($this->allPermission as $key => $value) {
                $permission[$key] = true;
            }
            foreach ($this->rootContent as $key => $value) {
                $contentPermission[$key] = true;
            }
        } else {
            $role = $this->getRole($user['roleid']);
            $permission = $role['permission'] ? json_decode($role['permission'], true) : [];
            $contentPermission = $role['content_permission'] ? json_decode($role['content_permission'], true) : [];

            if (is_array($permission)) {
                foreach ($permission as $key => $value) {
                    if (!isset($this->allPermission[$key])) {
                        unset($permission[$key]);
                    }
                }
            }
            if (is_array($contentPermission)) {
                foreach ($contentPermission as $key => $value) {
                    if (!isset($this->rootContent[$key])) {
                        unset($contentPermission[$key]);
                    }
                }
            }
            if (is_array($this->rootContent) && !is_array($contentPermission)) {
                $this->userContentIsAll = false;
            }
            if (count($contentPermission) != count($this->rootContent)) {
                $this->userContentIsAll = false;
            }

        }
        return [$permission, $contentPermission];
    }

    public function checkUserPermissionAccessController()
    {
        $permKey = '{' . strtolower($this->controller->routerPath) . ' ' . strtolower($this->controller->runMethod) . '}';
        $id = md5($permKey);

        if (isset($this->allPermission[$id]) && !$this->userPermission[$id]) {
            return ['code' => -1012];
        }
        return null;
    }
    // 如果key 不存在则允许访问.. 如果key 存在
    public function checkUserPermissionAccess($checkKey)
    {

        //如果没有权限表 则返回允许权限
        if (!isset($this->keyPermission[$checkKey])) {
            return true;
        }

        $checkKeyAry = explode('/', $checkKey);
        $tempKey = '';
        foreach ($checkKeyAry as $oneKey) {
            $tempKey .= ($tempKey == '' ? '' : '/') . $oneKey;
            $judgeAry[] = $tempKey;
        }
        $judgeAry[] = $checkKey . '{' . $this->controller->routerPath . ' ' . $this->controller->runMethod . '}';

        foreach ($judgeAry as $oneKey) {
            $id = md5(strtolower($checkKey));
            if (isset($this->allPermission[$id]) && $this->userPermission[$id]) {
                return true;
            }
        }

        return false;
    }
    public function getUserContent()
    {
        return $this->userContent;
    }

    public function getUserContentIsAll()
    {
        return $this->userContentIsAll;
    }

    public function checkUserContentAccess($checkKey)
    {

        //如果没有权限表 则返回允许权限
        if (!isset($this->rootContent[$checkKey])) {
            return true;
        }

        if (isset($this->userContent[$checkKey]) && $this->userContent[$checkKey]) {
            return true;
        }
        return false;
    }

    //获取用户信息
    public function getUser($uid)
    {
        return (array) DB::table('privileges_user')
            ->where('uid', $uid)
            ->limit(1)
            ->first();
    }

    //获取用户信息
    public function getUserByNickname($nickname, $noUid = null)
    {
        $where = [
            ['nickname', $nickname],
        ];
        if ($noUid) {
            $where[] = ['uid', '<>', $noUid];
        }
        return (array) DB::table('privileges_user')
            ->where($where)
            ->limit(1)
            ->first();
    }

    //获取角色信息
    public function getRole($roleid)
    {
        return (array) DB::table('privileges_role')
            ->where('roleid', $roleid)
            ->limit(1)
            ->first();
    }

    //根据名称获取角色名称
    public function getRoleByName($name, $noRoleid = null)
    {
        $where = [
            ['rolename', $name],
        ];
        if ($noRoleid) {
            $where[] = ['roleid', '<>', $noRoleid];
        }
        return (array) DB::table('privileges_role')
            ->where($where)
            ->limit(1)
            ->first();
    }


}
