# FAST FAST API by FF Framework

[TOC]

## 内容介绍

1. 支持自动在线生成接口文档,规范文档
2. 支持接口版本化
3. 规范的错误返回机制
4. 简单且高效率
5. 支持PHP7
6. MVC模式
7. 灵活的组件系统
8. 自由度高, 通过相应的写法 让接口 支持RestfulAPI等多种协议.
9. 支持 Token & Sign 双认证机制,并对以上机制进行了速度优化
10. 支持Swoole,提高QPS并发
11. 错误集中处理
12. 支持 Laravel 原生,查询器, ORM  三种方式

### 后续开发计划
1. 在Swoole启动变量下支持 异步 IO, 异步 Redis 异步Mysql 
2. 支持基于TCP协议的RPCAPI
3. 支持task
4. controller 支持 独立的地址传参
5. RESTful 快速开发

## 支持文档
### 一. 路由机制

FFAPI 项目必须`独立端口`或`独立域名` 不支持`路径方式`访问

#### 1.  Nginx重写规则
```
server {
    listen       80;
    
    
    #FFAPI 对应的 访问域名
    server_name  FFAPI_DOMAIN; 
    index  index.html index.htm index.php;
    
    #需要指定ffapi 项目下的run 目录
    root   home/webapps/ffapi/run/; 

    #重写规则
    location / {
    try_files $uri $uri/ /index.php$is_args$args;
    }

    location ~ \.php$ {
        include function/php.inc;
    }
}
```


#### 2. 路由规则
- 普通路由规则:
`http://{APIDOMAIN}/user/login` 对应 控制器地址 `controllers\userController.php` 下的 `actionLogin`方法

- 支持版本化的路由规则:
`http://{APIDOMAIN}/v1.0/user/login` 对应 控制器地址 `controllers\v1_0\userController.php` 下的 `actionLogin`方法

- 固定的内置的地址
Wiki 地址 : 
`http://{APIDOMAIN}/wiki` 对应  `controllers\wikiController.php` 下的 `actionIndex`方法
这里没有指定 `index` 方法,路由会自动调用控制器的 `默认方法`
如果需要设定当前控制器 `默认方法` 则需要设置 当前控制器的 `$defaultAction` 属性

	例如:
	```php
		// FILE: {HOMEROOT}/controllers/wikiController.php
		class wikiController extends Controller
		{
		    public $defaultAction = 'Index';
		    public function actionIndex()
		    {
			    //TODO.
		    }
		}
	```





### 二. 控制器说明
#### 1.控制器种类
#### 2. 控制器参数
#### 3. 获取外部接口参数
	```php
	$account = $this->request->vars['account'] ?? null;
	```
#### 4. 在 Controller 下获取token 用户信息
第一种方式 
>适用于基于token的控制器 例如 TokenAuthController 或 PrivilegesTokenAuthController

```
use ff\auth\TokenAuthController;
use ff\auth\PrivilegesTokenAuthController;
class democliController extends TokenAuthController
{
    public function actionTest()
    {
        $uid = $this->user->uid;
        //$this->user 下的属性 对应 user 表的字段
        return ['code' => 1, 'msg' => ''];
    }
}
```
第二种方式
> 适用于 全部控制器 
```
use ff\database\userModel;
class democliController extends Controller
{
    public function actionTest()
    {
        $userModel = new userModel;
        $uid = $userModel->uid;
        return ['code' => 1, 'msg' => ''];
    }
}
```


#### 5. 接口对接端传递Token令牌的两种方式(二选一)

- 在 `HTTP Header` 内增加 `Token` 来传递令牌信息

- 在 `GET`或`POST`等请求方式的请求参数内 增加 `Token` 参数 ( `优先级最高` )


### 三. 错误定义与文档标准化

#### 1. 错误定义

错误码需要在位置 `data/error.php` 中进行定义
错误码为 负数错误码对应的错误描述 的数组集合

例如:

```php

return [

    'code' => [
        //系统级错误
        -1000 => 'Access Denied!',
        -1001 => 'Interface Does Not Exist',
        -1002 => 'Method Not Allowed',
        -1003 => 'Sign Failed!',
        -1004 => 'Sign Expired!',
        -1005 => 'Not logged , Token is empty',
        -1006 => 'Token Authentication Failed',
        -1007 => 'Token expired',
        -1008 => 'Logger is disabled',
        -1009 => 'database error',
        -1010 => 'missing parameter',
        -1011 => 'parameter error',
        -1012 => 'You do not have permission to access',
        -1013 => 'You do not have permission to operate',
        -1014 => 'The associated user is not empty',
        -1015 => 'User exists',
        -1016 => 'Role does not exist',
        -1017 => 'You have chosen the wrong role',
        -1018 => 'You cannot delete yourself',
        -1019 => 'Nickname exists',
        -1020 => 'User does not exist',
        -1021 => 'Rolename exists',
        
        //业务级错误
        -2001 => 'Site does not exist',
        -2002 => 'orderSn does not exist',
        -2003 => 'goods sku does not exist',
        -2004 => '用户不存在或被禁用！',
        -2005 => '密码不正确',
    ],
    'level' => [
        '-1' => '系统级错误',
        '-2' => '业务级别错误',
    ],

];
```
在定义好错误码之后. 在对应的 Controller 内进行调用这个错误码

以登录接口为例:

```
class userController extends Controller
{
    public function actionLogin()
    {
        // $error 为业务的错误信息 例如参数 
        $account = $this->request->vars['account'] ?? null;

        if (!$account) {
            return ['code' => -1010]; //缺少参数
        }
    }
}
```
如果用户未提供 $account 参数访问接口则返回
```json
{"code":-1001,"msg":"Interface Does Not Exist","request_params":[],"request_dateline":1569556511.637888,"response_dateline":1569556511.734235}
```


#### 2. 文档标准化

书写规范

```php
	//文档的定义
    @name    {title}    {description}
    @method  {method}	{description}
    @format  {type}		{description}
    @param   {type}		[POST,GET,DELETE]{varname}	{is_require}	{description} 
    @var     {type}		{varname}	{description}
    @other   {description}
    @example {[format]} {(status:code)}
    @author  {info}
    
	//文档文字说明
    @name    //API文字描述
    @method  {method}	{description} //请求方式
    @return  {type}		{description} //返回格式
    @param   {type}		[POST,GET,DELETE]{varname}	{is_require}	{description}  //请求参数  is_require 取值范围 yes no
    @var     {type}		{varname}	{description}  //返回字段
    @other   {description}   //其他备注说明
    @example {[format]} {(status:code)} //返回示例   [format]  部分可以取消; status:可以取消 status取值范围 success error
    @author  {info} //作者


```

例如

```php
/**
 *
 * @name  用户登陆
 * @method POST
 * @return JSON
 * @param string [POST,GET,DELETE]account yes 账号
 * @param string[1,2] password yes 密码
 * @var int status 状态码 (成功 1 ;失败 0;)
 * @var string msg 状态信息
 * @other 本接口附带登陆COOKIE
 * @example
 * [POST][SUCCESS]JSON:{"status":1,"data":{"face":"63800205.jpg","name":"asd12","province":"266","city":"267","gender":"1","birthday":"1367401462","username":"admin"}}
 * @author haierspi
 * 
 */
```









###cli 方式运行 主要用于计划任务等命令行执行




格式为:
php -f {FrameworkIndexFile}  "{path}?{urlVars}" {request_method} "{bodyVars}"

例如: 

php -f ./index.php  "v1.0/user/login?var1=value1" POST "var1=value1"

{FrameworkIndexFile} : 框架入口文件
{path} : 路由路径
{urlVars} : URL参数; 这部分参数会自动解析为GET参数
{request_method} : 请求类型
{bodyVars} : 内容参数

另外注意各个类型

$this->request->vars['var1'] 获取的参数类型会不同;

例如: GET 请求下 Controller内 通过 $this->request->vars['var1'] 会获取 URL参数 的 var1 参数

具体获取类别请看如下列表:

各个请求方式使用的参数类型如下:
```
'GET' => 'urlVars',
'POST' => 'bodyVars',
'PUT' => 'bodyVars',
'PATCH' => 'bodyVars',
'DELETE' => 'urlVars',
'HEAD' => 'urlVars',
'OPTIONS' => 'urlVars',
'CLI' => 'urlVars',
```
当然也可以获取参数方法 强制修改获取参数类型例如

请求方式:GET
{urlVars} : var1=value1; 
{bodyVars} : var1=value2

$this->request->getVars('bodyVars'); 强制获取{内容参数}的参数清单,此例为 ['var1'=>'value2']
$this->request->getVars();  获取{默认方式}的参数清单,这里因为是GET请求,所以为参数类型为:urlVars,获得 ['var1'=>'value1']
获取指定参数
$this->request->getVar('var1'); 获取{默认方式}的参数var1,值为 "value1"
获取指定参数类型的指定参数
$this->request->getVar('var1','bodyVars'); 获取{内容参数}的参数var1,值为 "value2"

默认 GET请求方式 可以省略 

例如:

php -f ./index.php  "v1.0/user/login?var1=value1"
等价于
php -f ./index.php  "v1.0/user/login?var1=value1" GET 

另外有独立的 CLI请求方式 ,此CLI模式仅限于在CLI 方式才会执行.. 

例如 
```
class democliController extends Controller
{
    public function actionClitodo($method = 'CLI')
    {
        return ['code' => 1, 'msg' => ''];
    }
}
```
通过命令行工具执行 
```
php -f ./index.php  "v1.0/democli/clitodo?var1=value1" CLI
```
此时这个方法只能在 CLI 方式下运行




# 权限控制

权限功能的描述

```
1. 角色管理模块
    1)角色的列表  
        显示角色名称 角色的添加人,
        用户只能看见自己及其下级 创建的角色,管理员查看全部
        无分发权限用户无访问权限
    2)角色的添加  
        同一角色名 如已经存在 则不能进行添加操作
        添加的角色 权限清单 只能 等于或小于自身的权限..
        无分发权限用户无访问权限
    3)角色的编辑 
        用户只能编辑自己创建的角色
        无分发权限用户无访问权限
    4)角色的删除  
        用户只能删除 自己及其下级 创建的角色 (删除需要满足 角色下关联用户为0,否则不允许删除)
        无分发权限用户无访问权限

2. 授权用户管理模块
    1)授权用户的列表 
        显示 用户UID 用户昵称 角色名称 授权添加人, 
        用户只能看见 自己及其下级关联的 授权用户列表,管理员查看全部
        无分发权限用户无访问权限
    2)授权用户的添加  
        需要输入用户的昵称或者UID 以及 选择角色 进行关联, 
        用户只能添加并关联自己创建的角色, 管理员不受限制
        同一用户 如已经存在 则不能进行添加操作
        管理员可以设置 是否允许 分发 权限
        管理员可以设置 是否允许 二次分发 权限
        允许二次分发的用户 在添加 下级授权用户的时候 可以再次对 分发权限 和二次分发权限 进行设置 
        当用户添加下级授权用户时需要 从 最顶部 非管理员用户 依次检查 二次分发和分发权限.. 如果任意上级用户无权限 则下级无权限
    3)授权用户的编辑
        用户只能编辑 自己关联 的 授权用户 ( 用户不允许编辑下级添加的授权用户,管理员也同样不允许 )
        无分发权限用户无访问权限
    4)授权用户的删除
        用户只能删除 自己及其下级关联的  创建的授权用户
        无分发权限用户无访问权限
```

在控制器内试用 checkAccess 方法进行权限关键字判断 (bool)

```

use ff\auth\PrivilegesTokenAuthController;

class democliController extends PrivilegesTokenAuthController
{
    public function actionTest()
    {
        if (!$this->checkAccess('manage')) {
            return ['code' => -1012];
        }

        $uid = $this->user->uid;
        //$this->user 下的属性 对应 user 表的字段
        return ['code' => 1, 'msg' => ''];
    }
}
```

在控制器内使用 checkContent 方法进行 内容控制 判断 (bool)

```

use ff\auth\PrivilegesTokenAuthController;

class democliController extends PrivilegesTokenAuthController
{
    public function actionTest()
    {
        if ($this->checkContent('warehouse1')) {
            //仓库1的处理
        }

        if ($this->checkContent('warehouse2')) {
            //仓库2的处理
        }
        return ['code' => 1, 'msg' => ''];
    }
}
```


在控制器内使用 getContent 返回用户持有的内容控制关键字 列表 (array)

```

use ff\auth\PrivilegesTokenAuthController;

class democliController extends PrivilegesTokenAuthController
{
    public function actionTest()
    {
        $userC = $this->getContent();

        return ['code' => 1, 'msg' => ''];
    }
}
```

在控制器内使用 isAllContent 返回用户是否持有全部内容控制 (bool)

```

use ff\auth\PrivilegesTokenAuthController;

class democliController extends PrivilegesTokenAuthController
{
    public function actionTest()
    {
        if ($this->isAllContent) {
            //全部仓库
        }
        if ($this->checkContent('warehouse1')) {
            //仓库1的处理
        }
        return ['code' => 1, 'msg' => ''];
    }
}
```

# 分页使用
ff\helpers\Pager 类的使用

在控制器内的调用
```php
class privilegesController extends PrivilegesTokenAuthController
{
    public function actionUsers($method = 'GET|POST|PUT|DELETE')
    {

        $uid = (int) $this->request->vars['uid'];

        $testModel = new testModel();

        //实例化分页类
        $pager = new ff\helpers\Pager();

        //这里需要将pager实例 传给 model 方法
        $list = $testModel->getList( $pager);

        //获取返回的分页数据 pageData 会生成 ["page": 1,"pageNum": 10,"totalCount": 5] 数组
        $pagerData = $pager->getData();

        return ['code' => 1,'list' => $list, 'pager' => $pagerData]

    }
}
```
在model 里的调用方法

```php
class testModel
{
    //这里需要强制 $page 变量 为 \ff\helpers\Pager类的实例
    public function getList(\ff\helpers\Pager $pager = null)
    {

        //指定总数的计算方式 
        $pager->totalCount = function () {
            //这里this 指向的类为 testModel;具体请查阅 Closure 类
            $this->count();
        };

        //这里需要获取通过 $pager->getLimit() 和 $pager->getOffset() LIMIT 和 offset 相关参数值 
        $roles = DB::table('privileges_user')
            ->whereIn('uid', $uids)
            ->limit($pager->getLimit())
            ->offset($pager->getOffset())
            ->get()
            ->toArray();

        return (array) $roles;
    }
    //用户计算总数的方法
    public function count(){
        return 5;
    }
}

```
# ORM 相关

-  打印SQL

打印功能需要提前在 `config\config.php` 中打开 `SYSTEM_DEBUG_SQLLOG`
```php
//打印默认SQL
DB::getQueryLog();
//打印其他db SQL
DB::connection('master2')->getQueryLog(); 
```
一些说明



#REDIS 使用


1. Redis 原方法使用

    将框架 ff\nosql\redis 引入到脚本中.. 
    ```php
    use ff\nosql\redis;
    ```
    支持所有 redis 方法 例如 `redis::get`,`redis::hset` 等
    所有方法清单 参考: http://doc.redisfans.com/

    redis 配置 在 `data/config.php` 

    ```php
    'redis' => [
            'class' => 'ff\nosql\redis',
            'config' => [
                'default' => 'master',
                'master' => [
                    'server' => 'localhost',
                    'port' => 6379,
                    'pconnect' => 1,
                    'connect_timeout' => 0,
                    'password' => 'password',
                    'db' => 1,
                    'prefix' => '',
                ],
            ],
        ],
    ```
    其中 `redis.config.default` 用于指定默认连接 `key`

    也可以使用 `redis::connect('key')` 来进行脚本科幻 `redis` 库


2. Redis 封装缓存类

    设置缓存
    `rediscache::set`
 
    获取缓存
    `rediscache::get`

例如: (微信公众号TOKEN)
```php
if (($Token = rediscache::get('WXMP_SERVICE_TOKEN')) === false) {

    $resultConent = StringLib::geturlconent($this->urlparse(SELF::WX_TOKEN_URL));
    $resultData = json_decode($resultConent, 1);
    if (isset($resultData['errcode'])) {
    } else {
        $Token = $resultData['access_token'];
        rediscache::set('WXMP_SERVICE_TOKEN', $Token, $resultData['expires_in']);
    }
}
```

3. redis可视化桌面管理工具
   RedisDesktopManager : https://github.com/necan/RedisDesktopManager/releases



# SQL-Server 使用

#####  配置连接信息
     配置信息在   `data/config.php` 
       ```php
       'sqlsrv' => [
           'class' => 'ff\database\sqlsrvConnection',
           'config' => [
               //默认连接的数据库key
               'default' => 'master', 
               //数据库key为master的配置信息
               'master' => [
                   'host' => '',
                   'port' => '7698',
                   'username' => '',
                   'password' => '',
                   'database' => '',
                   'prefix' => '',
               ],
               //数据库key为online的配置信息
               'online' => [
                   'host' => '',
                   'port' => '7698',
                   'username' => 'sa',
                   'password' => '',
                   'database' => '',
                   'prefix' => '',
               ],
           ],
       ],
       ```
##### 编写 Model连接方法的两种方式
1) 继承 ff\database\sqlsrvConnection

    ```php
    namespace models\v1_0;

    use ff\database\sqlsrvConnection;

    //继承 ff\database\sqlsrvConnection

    class testSqlsrv  extends  sqlsrvConnection {



        public function getBalanceID()
        {


            //默认连接 
            $queryResource = $this->query("select * from B_Dictionary where CategoryID = 1;");

            while ($rowResult = $this->fetch($queryResource)) {
                $balances[$rowResult['DictionaryName']] = $rowResult['NID'];
            }

            //切换数据库
            $this->connect('online');

            $queryResource = $this->query("select * from B_Dictionary where CategoryID = 1;");

            while ($rowResult = $this->fetch($queryResource)) {
                $balances[$rowResult['DictionaryName']] = $rowResult['NID'];
            }



            return  $balances;
        }
    }

    ```
2) 直接指定框架组件 \ff::$app->sqlsr

    ```php
    namespace models\v1_0;

    class testSqlsrv {

        class testSqlsrv {

            public function getBalanceID()
            {

                $queryResource = \ff::$app->sqlsrv->query("select * from B_Dictionary where CategoryID = 1;");

                while ($rowResult = \ff::$app->sqlsrv->fetch($queryResource)) {
                    $balances[$rowResult['DictionaryName']] = $rowResult['NID'];
                }

                return  $balances;
            }
        }
    }

    ```
##### 代码demo
 `controllers\v1_0\testController.php`  中的  `actionSqlsrv` 方法


