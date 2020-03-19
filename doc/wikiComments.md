# API WIKI 编写规范

```php
    @name    {title}    {description}
    @method  {method}	{description}
    @format  {type}		{description}
    @param   {type}		[POST,GET,DELETE]{varname}	{is_require}	{description} 
    @var     {type}		{varname}	{description}
    @other   {description}
    @example {[format]} {(status:code)}
    @author  {info}
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

注释说明
```php
@name    //API文字描述
@method  {method}	{description} //请求方式
@return  {type}		{description} //返回格式
@param   {type}		[POST,GET,DELETE]{varname}	{is_require}	{description}  //请求参数  is_require 取值范围 yes no
@var     {type}		{varname}	{description}  //返回字段
@other   {description}   //其他备注说明
@example {[format]} {(status:code)} //返回示例   [format]  部分可以取消; status:可以取消 status取值范围 success error
@author  {info} //作者
```

