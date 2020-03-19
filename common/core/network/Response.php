<?php
namespace ff\network;

use ff;
use ff\base\Exception;

class Response
{
    const MODE_CGI = 0;
    const MODE_SWOOLE = 1;
    const MODE_CLI = 2;
    private $responseObject;
    private $responseObjectType;
    public $outVars = [];
    private $router;
    private $outputPrint;

    public $httpStatuses = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        118 => 'Connection timed out',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        208 => 'Already Reported',
        210 => 'Content Different',
        226 => 'IM Used',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Reserved',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',
        310 => 'Too many Redirect',
        400 => 'Bad response',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'response Time-out',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'response Entity Too Large',
        414 => 'response-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'responseed range unsatisfiable',
        417 => 'Expectation failed',
        418 => 'I\'m a teapot',
        421 => 'Misdirected response',
        422 => 'Unprocessable entity',
        423 => 'Locked',
        424 => 'Method failure',
        425 => 'Unordered Collection',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many responses',
        431 => 'response Header Fields Too Large',
        449 => 'Retry With',
        450 => 'Blocked by Windows Parental Controls',
        451 => 'Unavailable For Legal Reasons',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway or Proxy Error',
        503 => 'Service Unavailable',
        504 => 'Gateway Time-out',
        505 => 'HTTP Version not supported',
        507 => 'Insufficient storage',
        508 => 'Loop Detected',
        509 => 'Bandwidth Limit Exceeded',
        510 => 'Not Extended',
        511 => 'Network Authentication Required',
    ];

    public function __construct($objectType = self::MODE_CGI)
    {
        $this->responseObjectType = $objectType;

    }

    public function router_output($outVars, $router = null)
    {
        $this->router = $router;
        $this->outVars = $outVars;
        return $this->output($outVars);
    }

    public function init($responseObject = null)
    {
        if (self::MODE_CGI == $this->responseObjectType) {
            $this->responseObject = new ResponseFastcgiResource();
        } elseif (self::MODE_CLI == $this->responseObjectType) {
            $this->responseObject = new ResponseCliResource();
        }
        //swoole
        elseif (self::MODE_SWOOLE == $this->responseObjectType) {
            $this->responseObject = $responseObject;
        }

    }

    public function varsCodeParse()
    {
        if (!isset($this->outVars['code'])) {
            return;
        }
        $statusCode = $this->outVars['code'];
        if (isset($this->httpStatuses[$statusCode])) {
            header("HTTP/1.1 {$statusCode} {$this->httpStatuses[$statusCode]}");
        } else {
            $codeErrors = require SYSTEM_ROOT_PATH . '/data/error.php';

            $this->outVars['code'] = (int) $this->outVars['code'];
            $code = $this->outVars['code'];

            if (isset($codeErrors['code'][$code])) {
                $this->outVars['msg'] = $codeErrors['code'][$code];
            }
        }
    }

    public function outFormat()
    {
        if (is_string($this->outVars)) {
            return $this->outVars;
        }
        $format = ff::$force_outputformat ?: ($this->router->_FORMAT ?: key(ff::$config['response']));

        if (isset(ff::$config['response'][$format])) {
            $format = ff::createObject(ff::$config['response'][$format]);
            return $format($this->outVars, $this->responseObject);
        } else {
            $this->responseObject->status(404);
            $this->header('Status', '404 Not Found');

        }
    }
    public function varsTime()
    {
        $this->outVars['request_dateline'] = SYSTEM_BEGIN_TIME;
        $this->outVars['response_dateline'] = microtime(true);
    }

    public function varsresponse()
    {
        $this->outVars['request_params'] = $this->router->request->vars;
    }

    public function apiheader()
    {
        $this->header('Access-Control-Allow-Credentials', 'true');
        $this->header("Access-Control-Allow-Headers", 'Origin, X-Requested-With, Token, Content-Type, Accept, Authorization');

        if (isset($this->router->request->vars['domain'])) {
            if (preg_match('/^http|https:\/\//is', $this->router->request->vars['domain'])) {
                $domain = $this->router->request->vars['domain'];
            } else {
                $domain = 'http://' . $this->router->request->vars['domain'];
            }
            $this->header('Access-Control-Allow-Origin', $domain);
        } else {
            $this->header('Access-Control-Allow-Origin', '*');
        }
    }

    public function header($key, $value, $ucwords = true)
    {
        $this->responseObject->header($key, $value, $ucwords);
    }

    private function output()
    {
        if (is_array($this->outVars)) {
            $this->apiheader();
            $this->varsCodeParse();

            $this->varsresponse();
            $this->varsTime();
        }
        $output = $this->outFormat();
        
        //$this->header('Content-Length',strlen($output));

        if (self::MODE_CGI == $this->responseObjectType) {
            echo $output;
        } elseif (self::MODE_CLI == $this->responseObjectType) {
            echo $output . "\n";
        }
        return $output;
    }
}

//兼容php5.6
class ResponseFastcgiResource
{
    public $code = null;
    public function status($code = null)
    {
        $this->code = $code;
    }
    public function header($key, $value, $ucwords = true)
    {
        if ($ucwords) {
            $keyArray = explode('-', $key);
            foreach ($keyArray as $k => $v) {
                $keyArray[$k] = ucfirst($v);
            }
            $key = join('-', $keyArray);
        }
        header($key . ' :' . $value, true, $this->code);
    }
}

class ResponseCliResource
{

    public function status($code)
    {
        //nothing todo
    }
    public function header($key, $value, $ucwords = true)
    {
        //nothing todo
    }
}
