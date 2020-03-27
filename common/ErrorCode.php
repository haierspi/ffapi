<?php
namespace common;

use ff\code\ErrorCode as ErrorCodeFF;

/**
 * 应用级错误代码
 */
class ErrorCode extends ErrorCodeFF
{

    const NO_PERMISSION_OPERATE = -1013; // You do not have permission to operate;
    const ASSOCIATED_USER_EXIST = -1014; // The associated user is not empty;
    const USER_EXIST = -1015; // User exists;
    const ROLE_NOT_EXIST = -1016; // Role does not exist;
    const WRONG_ROLE = -1017; // You have chosen the wrong role;
    const DELETE_YOURSELF = -1018; // You cannot delete yourself;
    const NICKNAME_EXIST = -1019; // Nickname exists;
    const USER_NOT_EXIST = -1020; // User does not exist;
    const ROLENAME_EXIST = -1021; // Rolename exists;

    const GOODS_SKU_EXIST = -2003; //goods sku does not exist
    const USER_ERROR = -2004; //用户不存在或被禁用！
    const PASSWORD_ERROR = -2005; //密码不正确
    const BLOCK_WORD_EXIST = -4001; //屏蔽关键字已经存在
    const BLOCK_WORD_NOT_EXIST = -4002; //屏蔽关键字不存在
    const APP_NOT_EXIST = -4003; //应用不存在
    const BLOCK_WORD_ERROR = -4004; //屏蔽关键字不能含有百分号(%)或下横线(_)
    const COMMENT_NOT_EXIST = -4005; //该评论不存在
    const COMMENT_UNABLE_DISPLAY_OPERATE = -4006; //该评论不能被设置隐藏/显示操作
    const COMMENT_WAIT_REPEAT_OPERATE = -4007; //该评论上一次操作尚未执行完毕,执行期间不能再次操作
    
    const SAVE_FAIL = -5001; //保存失败
    const NO_DATA = -5002; //无数据
    const OPERATE_FAIL = -5003; //操作失败
    const USER_EXIST_DATA = -5004; //该开发员已存在数据
    const POSITION_EXIST = -5005; //部位名称已存在
    const REQUEST_MODE_ERROR = -5101; //请求方式错误
    const DUPLICATE_NAME = -5102; //名称重复

    // const   =    -5103; //参数错误
    //  const  _MSG =    '参数错误';

    /**
     *  MSG Content
     */

    const NO_PERMISSION_OPERATE_MSG = 'You do not have permission to operate;';
    const ASSOCIATED_USER_EXIST_MSG = 'The associated user is not empty;';
    const USER_EXIST_MSG = 'User exists;';
    const ROLE_NOT_EXIST_MSG = 'Role does not exist;';
    const WRONG_ROLE_MSG = 'You have chosen the wrong role;';
    const DELETE_YOURSELF_MSG = 'You cannot delete yourself;';
    const NICKNAME_EXIST_MSG = 'Nickname exists;';
    const USER_NOT_EXIST_MSG = 'User does not exist;';
    const ROLENAME_EXIST_MSG = 'Rolename exists;';
    const GOODS_SKU_EXIST_MSG = 'goods sku does not exist';
    const USER_ERROR_MSG = '屏蔽关键字已经存在';
    const PASSWORD_ERROR_MSG = '屏蔽关键字已经存在';
    const BLOCK_WORD_EXIST_MSG = '屏蔽关键字已经存在';
    const BLOCK_WORD_NOT_EXIST_MSG = '屏蔽关键字不存在';
    const APP_NOT_EXIST_MSG = '应用不存在';
    const BLOCK_WORD_ERROR_MSG = '屏蔽关键字不能含有百分号(%)或下横线(_)';
    const COMMENT_NOT_EXIST_MSG = '该评论不存在';
    const COMMENT_UNABLE_DISPLAY_OPERATE_MSG = '该评论不能被设置隐藏/显示操作';
    const COMMENT_WAIT_REPEAT_OPERATE_MSG = '该评论上一次操作尚未执行完毕,执行期间不能再次操作';
    const SAVE_FAIL_MSG = '保存失败';
    const NO_DATA_MSG = '无数据';
    const OPERATE_FAIL_MSG = '操作失败';
    const USER_EXIST_DATA_MSG = '该开发员已存在数据';
    const POSITION_EXIST_MSG = '部位名称已存在';
    const REQUEST_MODE_ERROR_MSG = '请求方式错误';
    const DUPLICATE_NAME_MSG = '名称重复';

}
