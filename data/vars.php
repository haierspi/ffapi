<?php

return [

    


    'pager' => [
        'title' => '分页数据',
        'type' => 'object',
        'data' => [
            'page' => '当前页数',
            'pageNum' => '当前每页数据量',
            'totalCount' => '当前LIST总计数量',
        ],
    ],
    'orderData' => [
        'title' => '客户投诉订单信息',
        'type' => 'object',
        'data' => [
            'orderId' => '订单ID',
            'orderMoney' => '订单总金额(包含运费)',
            'goodsNum' => '订单内商品数量',
            'usaPayTime' => '订单美国付款时间',
        ],
    ],

    'complaintOrderGoods' => [
        'title' => '客户投诉订单商品信息',
        'type' => 'array_object',
        'data' => [
            'recId' => '订单商品唯一ID',
            'orderId' => '订单号',
            'goodsId' => '订单商品ID',
            'goodsName' => '订单商品名称',
            'goodsSn' => 'YL170609303',
            'goodsSku' => '订单商品SKU',
            'productId' => '',
            'goodsNumber' => '销售数量',
            'goodsAttr' => '',
            'goodsThumb' => '订单商品缩略图',
            'goodCatId' => '分类ID',
        ],
    ],

    'users' => [
        'title' => '用户列表清单',
        'type' => 'array_object',
        'data' => [
            "uid"=> "用户UID",
            "username"=>"用户名",
            "nickname"=>"用户昵称",
            "roleid"=>"角色ID",
            "rolename"=>"角色名称",
            "is_distribute"=>"是否拥有分发权限 0:没有 1:有",
            "is_have_distribute"=>"是否拥有二次分发权限 0:没有 1:有 ",
            "is_root"=>"是否是管理员 0:否 1:是",
            "create_uid"=>"父级创建人",
            "parent_tree"=>"父级角色树",
            "user_tree"=>"用户角色树",
            "create_datetime"=>"创建时间"
        ],
    ],
    
    'user' => [
        'title' => '用户管理数据',
        'type' => 'object',
        'data' => [
            "uid"=> "用户UID",
            "username"=>"用户名",
            "nickname"=>"用户昵称",
            "roleid"=>"角色ID",
            "is_distribute"=>"是否拥有分发权限 0:没有 1:有",
            "is_have_distribute"=>"是否拥有二次分发权限 0:没有 1:有 ",
            "is_root"=>"是否是管理员 0:否 1:是",
            "create_uid"=>"父级创建人",
            "parent_tree"=>"父级角色树",
            "user_tree"=>"用户角色树",
            "create_datetime"=>"创建时间"
        ],
    ],

    'userData' => [
        'title' => '用户数据',
        'type' => 'object',
        'data' => [
            'uid'=>"UID",
            'user_type'=>"用户类型",
            'nickname'=>"昵称",
            'username'=>"用户名",
            'email'=>"邮箱",
            'email_bind'=>"邮箱是否验证 0否 1是",
            'mobile'=>"手机号",
            'mobile_bind'=>"手机是否验证 0否 1是",
            'avatar'=>"头像",
            'score'=>"积分",
            'money'=>"余额 0.00",
            'reg_ip'=>"注册IP",
            'reg_type'=>"注册方式",
            'create_time'=>"创建时间 438651748",
            'update_time'=>"更新时间 1566791266",
            'status'=>"状态",
            'short'=>"简称 AD",
        ],
    ],

    'roles' => [
        'title' => '角色列表',
        'type' => 'array_object',
        'data' => [
            "roleid"=>"角色ID",
            "rolename"=>"角色名称",
            "permission"=>"权限",
            "content_permission"=>"用户权限",
            "create_uid"=>"创建人UID",
            "create_datetime"=>"创建时间",
            "update_datetime"=>"修改时间"
        ],
    ],

    'omsUser' => [
        'title' => 'OMS管理用户清单',
        'type' => 'array_object',
        'data' => [
            'uid'=>"UID",
            'user_type'=>"用户类型",
            'nickname'=>"昵称",
            'username'=>"用户名",
            'email'=>"邮箱",
            'email_bind'=>"邮箱是否验证 0否 1是",
            'mobile'=>"手机号",
            'mobile_bind'=>"手机是否验证 0否 1是",
            'avatar'=>"头像",
            'score'=>"积分",
            'money'=>"余额 0.00",
            'reg_ip'=>"注册IP",
            'reg_type'=>"注册方式",
            'create_time'=>"创建时间 438651748",
            'update_time'=>"更新时间 1566791266",
            'status'=>"状态",
            'short'=>"简称 AD",
        ],
    ],

    'commentBannedWords' => [
        'title' => 'fb应用列表',
        'type' => 'object',
        'data' => [
            'bwId' =>  '关键字ID',
            'uid' =>  '操作人UID',
            'username' =>  '操作人名称',
            'word' =>  '屏蔽关键字',
            'createdTime' =>  '创建日期',
            'updatedTime' =>  '更新时间',
        ],
    ],

    'facebookApps' => [
        'title' => 'fb应用列表',
        'type' => 'array_object',
        'data' => [
            'fbAppId' =>  '内部操作ID',
            'appId' =>  '应用ID',
            'appSecret' =>  '应用密钥',
            'accessToken' =>  '应用访问令牌',
            'type' =>  '应用类型 [marketing,page]',
            'title' =>  '应用标注标题',
            'createdTime' =>  '创建日期',
            'updatedTime' =>  '更新日期',
            'data' =>  '和 fb 令牌相关的关联数据',
            'tags' =>  '标签,多个使用逗号间隔',
            'uid' =>  '操作人UID',
            'username' =>  '操作人名称',
        ],
    ],

    'pageComments' => [
        'title' => 'fb评论列表',
        'type' => 'array_object',
        'data' => [
            'commentPrId' =>  '评论主键ID',
            'commentId' =>  '评论ID[fb]',
            'commentType' =>  '评论类型[0 帖子评论 1 评论的评论]',
          
            'pageId' =>  '站点ID',
            'postId' =>  '帖子ID',
            'postType' =>  '帖子类型[0 普通帖子 1 广告帖子]',
          
            'adCreativeId' =>  'Ad 创意 ID',
            'accountId' =>  'Ad 账户 ID',
            'adId' =>  'Ad ID',
          
            'canHide' =>  '是否允许隐藏[0 不允许 1 允许]',
            'isHidden' =>  '是否隐藏[0 显示 1 隐藏]',
            'taskType' =>  '执行任务类型[-1 未设置任务 0 显示任务 1 隐藏任务 2 删除任务]',
          
            'replyCommentId' =>  '回复的帖子ID',
            'message' =>  '站点介绍',
            'fromId' =>  '来源用户ID [fb]',
            'fromName' =>  '来源用户名称 [fb]',
            'link' =>  '连接地址',
            'data' =>  'page 其他数据原始数据',
            
            'commentCreatedTime' =>  '评论创建日期',
            'commentCreatedTimeZone' =>  '评论创建日期时区',
          
            'createdTime' =>  '创建日期',
            'updatedTime' =>  '更新日期',
        ],
    ],

    
    
    'pages' => [
        'title' => 'fb主页列表',
        'type' => 'array_object',
        'data' => [
            'pageId' =>  '站点ID',
            'pageName' =>  '站点名称',
            'pageAbout' =>  '站点介绍',
            'pageAccessToken' =>  '站点token',
            'data' =>  'page 其他数据原始数据',
            'status' =>  '状态 0:失效的 1:生效的',
            'createdTime' =>  '创建日期',
            'updatedTime' =>  '更新日期',
        ],
    ],


];
