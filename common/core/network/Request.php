<?php
namespace ff\network;

class Request
{
    const MODE_CGI = 0;
    const MODE_SWOOLE = 1;
    const MODE_CLI = 2;
    public $method = '';
    public $requestPath = '';
    public $vars = [];
    public $urlVars = [];
    public $bodyVars = [];
    public $fileVars = [];
    public $cookieVars = [];
    public $headerVars = [];
    public $clientip = '';
    public $urlVarsRAW = [];
    public $bodyVarsRAW = [];
    private $requestObject;
    private $requestObjectType;

    private $urlExcludeVars = ['_VERSION', '_CONTROLLER', '_ACTION', '_ACTION_PARAMS', '_FORMAT'];
    private $methodMap = [
        'GET' => 'urlVars',
        'POST' => 'bodyVars',
        'PUT' => 'bodyVars',
        'PATCH' => 'bodyVars',
        'DELETE' => 'urlVars',
        'HEAD' => 'urlVars',
        'OPTIONS' => 'urlVars',
        'CLI' => 'urlVars',
    ];

    public function __construct($objectType = self::MODE_CGI)
    {
        $this->requestObjectType = $objectType;
    }

    public function init($requestObject = null)
    {

        if (self::MODE_CGI == $this->requestObjectType) {
            $this->requestObject = &$_SERVER;
            $this->requestPath = $_SERVER['REQUEST_URI'];
            $this->method = $this->requestObject['REQUEST_METHOD'];
            $this->urlVars = $_GET;
            $this->bodyVarsRAW = file_get_contents("php://input");
            $this->cookieVars = $_COOKIE;
        }
        //cli
        elseif (self::MODE_CLI == $this->requestObjectType) {
            $parseData = parse_url($_SERVER['argv'][1]);
            $this->requestObject = &$_SERVER;
            $this->requestPath = preg_match('/^\//', $parseData['path']) ? $parseData['path'] : '/' . $parseData['path'];
            $this->method = $_SERVER['argv'][2] ? $_SERVER['argv'][2] : 'GET';
            parse_str($parseData['query'], $this->urlVars);
            $this->bodyVarsRAW = $_SERVER['argv'][3];
            $this->cookieVars = [];
        }
        //swoole cli
        elseif (self::MODE_SWOOLE == $this->requestObjectType) {
            $this->requestObject = $requestObject;
            $this->requestPath = $this->requestObject->server['request_uri'];
            $this->method = $this->requestObject->server['request_method'];
            $this->urlVars = $this->requestObject->get;
            $this->bodyVarsRAW = http_build_query($this->requestObject->post);
            $this->cookieVars = $this->requestObject->cookie;
        }

        if ($this->method == 'CLI' && self::MODE_CLI != $this->requestObjectType) {
            $this->method = '';
        }
        $this->getHeaderVars();
        $this->getUrlVars();
        $this->getbodyVars();
        $this->vars = $this->getVars();
        $this->clientip = $this->getUserIP();

    }

    public function getVars($varType = null)
    {
        $varType = $varType ?: $this->methodMap[$this->method];
        return $this->{$varType};
    }

    public function getVar($var, $varType = null)
    {
        $varType = $varType ?: $this->methodMap[$this->method];
        return $this->{$varType}[$var];
    }

    private function getHeaderVars()
    {
        if (self::MODE_CGI == $this->requestObjectType) {
            foreach ($this->requestObject as $name => $value) {
                if (substr($name, 0, 5) == 'HTTP_') {
                    $key = str_replace(' ', '-', strtolower(str_replace('_', ' ', substr($name, 5))));
                    if ($key == 'content-type') {
                        list($ctype, $data) = explode(';', $value);
                        $this->headerVars['content-type'] = $ctype;
                        if ($data) {
                            list($ctypekey, $ctypevalue) = explode('=', $data);
                            $this->headerVars[$ctypekey] = $ctypevalue;
                        }
                    } else {
                        $this->headerVars[$key] = $value;
                    }
                } elseif ($name == 'REMOTE_ADDR') {
                    $this->headerVars['remote_addr'] = $value;
                }
            }

        }
        //swoole
        elseif (self::MODE_SWOOLE == $this->requestObjectType) {
            $this->headerVars = $this->requestObject->header;
            $this->headerVars['remote_addr'] = $this->requestObject->server['remote_addr'];
        }
    }

    private function getUrlVars()
    {
        foreach ($this->urlExcludeVars as $var) {
            unset($this->urlVars[$var]);
        }
        $this->urlVarsRAW = join('&', $this->urlVars);
    }

    private function getBodyVars()
    {

        if (self::MODE_CLI == $this->requestObjectType) {
            parse_str($this->bodyVarsRAW, $this->bodyVars);
        } else {

            if (isset($this->headerVars['content-type']) && $this->headerVars['content-type'] == 'application/json') {
                $this->bodyVars = json_decode($this->bodyVarsRAW, true);
            } elseif (isset($this->headerVars['content-type']) && $this->headerVars['content-type'] == 'application/x-www-form-urlencoded') {
                parse_str($this->bodyVarsRAW, $this->bodyVars);
            } elseif (isset($this->headerVars['content-type']) && stripos($this->headerVars['content-type'], 'multipart/form-data') !== false) {
                //fastcgi
                if (self::MODE_CGI == $this->requestObjectType) {
                    $bodyVars = $_POST;
                    $fileVars = $_FILES;
                }
                //swoole cli
                elseif (self::MODE_SWOOLE == $this->requestObjectType) {
                    $bodyVars = $this->requestObject->post;
                    $fileVars = [];
                    if ($this->requestObject->files) {
                        foreach ($this->requestObject->files as $fileVar => $fileData) {
                            if (empty($fileData['name'])) {
                                foreach ($fileData as $key => $files) {
                                    foreach ($files as $name => $value) {
                                        $fileVars[$fileVar][$name][$key] = $value;
                                    }
                                }
                            } else {
                                $fileVars[$fileVar] = $fileData;
                            }
                        }
                    }
                }
                $this->bodyVars = $bodyVars;
                $this->fileVars = $fileVars;
            } else {
                $this->bodyVarsRAW = '';
                $this->bodyVars = [];
            }
        }

    }

    public function getUserIP()
    {
        //REMOTE_ADDR
        $ip = $this->headerVars['remote_addr'];
        //HTTP_CLIENT_IP
        if (isset($this->headerVars['client_ip']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $this->headerVars['client_ip'])) {
            $ip = $this->headerVars['client_ip'];
        }
        //HTTP_X_FORWARDED_FOR
        elseif (isset($this->headerVars['x_forwarded_for']) and preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s', $this->headerVars['x_forwarded_for'], $matches)) {
            foreach ($matches[0] as $xip) {
                if (!preg_match('#^(10|172\.16|192\.168)\.#', $xip)) {
                    $ip = $xip;
                    break;
                }
            }
        }
        return $ip;
    }

    //--------------------------

    protected function resolveRequestUri()
    {
        if (isset($this->requestObject['HTTP_X_REWRITE_URL'])) { // IIS
            $requestUri = $this->requestObject['HTTP_X_REWRITE_URL'];
        } elseif (isset($this->requestObject['REQUEST_URI'])) {
            $requestUri = $this->requestObject['REQUEST_URI'];
            if ($requestUri !== '' && $requestUri[0] !== '/') {
                $requestUri = preg_replace('/^(http|https):\/\/[^\/]+/i', '', $requestUri);
            }
        } elseif (isset($this->requestObject['ORIG_PATH_INFO'])) { // IIS 5.0 CGI
            $requestUri = $this->requestObject['ORIG_PATH_INFO'];
            if (!empty($this->requestObject['QUERY_STRING'])) {
                $requestUri .= '?' . $this->requestObject['QUERY_STRING'];
            }
        } else {
            throw new InvalidConfigException('Unable to determine the request URI.');
        }

        return $requestUri;
    }

    public function getQueryString()
    {
        return isset($this->requestObject['QUERY_STRING']) ? $this->requestObject['QUERY_STRING'] : '';
    }

    public function getIsSecureConnection()
    {
        return isset($this->requestObject['HTTPS']) && (strcasecmp($this->requestObject['HTTPS'], 'on') === 0 || $this->requestObject['HTTPS'] == 1)
        || isset($this->requestObject['HTTP_X_FORWARDED_PROTO']) && strcasecmp($this->requestObject['HTTP_X_FORWARDED_PROTO'], 'https') === 0;
    }

    public function getServerName()
    {
        return $this->requestObject['SERVER_NAME'];
    }

    public function getServerPort()
    {
        return (int) $this->requestObject['SERVER_PORT'];
    }

    public function getReferrer()
    {
        return isset($this->requestObject['HTTP_REFERER']) ? $this->requestObject['HTTP_REFERER'] : null;
    }

    public function getUserAgent()
    {
        return isset($this->requestObject['HTTP_USER_AGENT']) ? $this->requestObject['HTTP_USER_AGENT'] : null;
    }

    public function getUserHost()
    {
        return isset($this->requestObject['REMOTE_HOST']) ? $this->requestObject['REMOTE_HOST'] : null;
    }

    public function getAuthUser()
    {
        return isset($this->requestObject['PHP_AUTH_USER']) ? $this->requestObject['PHP_AUTH_USER'] : null;
    }

    public function getAuthPassword()
    {
        return isset($this->requestObject['PHP_AUTH_PW']) ? $this->requestObject['PHP_AUTH_PW'] : null;
    }

    private $_port;

    public function getPort()
    {
        if ($this->_port === null) {
            $this->_port = !$this->getIsSecureConnection() && isset($this->requestObject['SERVER_PORT']) ? (int) $this->requestObject['SERVER_PORT'] : 80;
        }

        return $this->_port;
    }

    private $_securePort;

    public function getSecurePort()
    {
        if ($this->_securePort === null) {
            $this->_securePort = $this->getIsSecureConnection() && isset($this->requestObject['SERVER_PORT']) ? (int) $this->requestObject['SERVER_PORT'] : 443;
        }

        return $this->_securePort;
    }

    private $_contentTypes;

    public function getAcceptableContentTypes()
    {
        if ($this->_contentTypes === null) {
            if (isset($this->requestObject['HTTP_ACCEPT'])) {
                $this->_contentTypes = $this->parseAcceptHeader($this->requestObject['HTTP_ACCEPT']);
            } else {
                $this->_contentTypes = [];
            }
        }

        return $this->_contentTypes;
    }

    public function getContentType()
    {
        if (isset($this->requestObject["CONTENT_TYPE"])) {
            return $this->requestObject["CONTENT_TYPE"];
        } elseif (isset($this->requestObject["HTTP_CONTENT_TYPE"])) {
            return $this->requestObject["HTTP_CONTENT_TYPE"];
        }
        return null;
    }

    private $_languages;

    public function getAcceptableLanguages()
    {
        if ($this->_languages === null) {
            if (isset($this->requestObject['HTTP_ACCEPT_LANGUAGE'])) {
                $this->_languages = array_keys($this->parseAcceptHeader($this->requestObject['HTTP_ACCEPT_LANGUAGE']));
            } else {
                $this->_languages = [];
            }
        }

        return $this->_languages;
    }

    public function getPreferredLanguage(array $languages = [])
    {
        if (empty($languages)) {
            return Yii::$app->language;
        }
        foreach ($this->getAcceptableLanguages() as $acceptableLanguage) {
            $acceptableLanguage = str_replace('_', '-', strtolower($acceptableLanguage));
            foreach ($languages as $language) {
                $normalizedLanguage = str_replace('_', '-', strtolower($language));

                if ($normalizedLanguage === $acceptableLanguage || // en-us==en-us
                    strpos($acceptableLanguage, $normalizedLanguage . '-') === 0 || // en==en-us
                    strpos($normalizedLanguage, $acceptableLanguage . '-') === 0) { // en-us==en

                    return $language;
                }
            }
        }

        return reset($languages);
    }

    public function getETags()
    {
        if (isset($this->requestObject['HTTP_IF_NONE_MATCH'])) {
            return preg_split('/[\s,]+/', str_replace('-gzip', '', $this->requestObject['HTTP_IF_NONE_MATCH']), -1, PREG_SPLIT_NO_EMPTY);
        } else {
            return [];
        }
    }

}
