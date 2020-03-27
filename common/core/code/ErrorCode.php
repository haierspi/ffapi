<?php
namespace ff\code;

/**
 * System Level Error Code
 */
class ErrorCode extends BaseCode
{

    const ACCESS_DENIED = -1000; // Access Denied!
    const INTERFACE_NOT_EXIST = -1001; //Interface Does Not Exist;
    const METHOD_NOT_ALLOWED = -1002; // Method Not Allowed;
    const SIGN_FAILED = -1003; // Sign Failed!;
    const SIGN_EXPIRED = -1004; // Sign Expired!;
    const NOT_LOGGED = -1005; // Not logged ; Token is empty;
    const TOKEN_FAILED = -1006; // Token Authentication Failed;
    const TOKEN_EXPIRED = -1007; // Token expired;
    const LOGGER_DISABLED = -1008; // Logger is disabled;
    const DATABASE_ERROR = -1009; // database error;
    const MISSING_PARAMETER = -1010; // missing parameter;
    const PARAMETER_ERROR = -1011; // parameter error;
    const NO_PERMISSION_ACCESS = -1012;


    /**
     *  MSG Content
     */
    
    const ACCESS_DENIED_MSG = 'Access Denied!';
    const INTERFACE_NOT_EXIST_MSG = 'Interface Does Not Exist';
    const METHOD_NOT_ALLOWED_MSG = 'Method Not Allowed';
    const SIGN_FAILED_MSG = 'Sign Failed!';
    const SIGN_EXPIRED_MSG = 'Sign Expired!';
    const NOT_LOGGED_MSG = 'Not logged ; Token is empty';
    const TOKEN_FAILED_MSG = 'Token Authentication Failed';
    const TOKEN_EXPIRED_MSG = 'Token expired';
    const LOGGER_DISABLED_MSG = 'Logger is disabled';
    const DATABASE_ERROR_MSG = 'database error';
    const MISSING_PARAMETER_MSG = 'missing parameter';
    const PARAMETER_ERROR_MSG = 'parameter error';
    const NO_PERMISSION_ACCESS_MSG = 'no permission access';


}
