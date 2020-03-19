<?php
namespace ff\network;

class RPC
{
    public $method = '';
    public $vars = [];
    public $urlVars = [];
    public $bodyVars = [];
    public $cookieVars = [];
    public $headerVars = [];
    public $clientip = '';
    public $urlVarsRAW = [];
    public $bodyVarsRAW = [];

    private $urlExcludeVars = ['_VERSION', '_CONTROLLER', '_ACTION', '_ACTION_PARAMS', '_FORMAT'];
    private $methodMap = [
        'GET' => 'urlVars',
        'POST' => 'bodyVars',
        'PUT' => 'bodyVars',
        'DELETE' => 'urlVars',
        'HEAD' => 'urlVars',
        'OPTIONS' => 'urlVars',
        'PATCH' => 'bodyVars',
    ];

    public function __construct()
    {
        $this->method = $_SERVER['REQUEST_METHOD'];
        $this->getHeaderVars();
        $this->getUrlVars();
        $this->getbodyVars();
        $this->getCookieVars();
        $this->vars = $this->{$this->methodMap[$this->method]};
        $this->clientip = $this->getUserIP();
    }

    public function getVars($method = null)
    {
        $method = $method ?: $this->method;
        return $this->{$this->methodMap[$method]};
    }

    private function getHeaderVars()
    {
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $key = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
                if ($key == 'Content-Type') {
                    list($ctype, $data) = explode(';', $value);
                    $this->headerVars['Content-Type'] = $ctype;
                    if ($data) {
                        list($ctypekey, $ctypevalue) = explode('=', $data);
                        $this->headerVars[$ctypekey] = $ctypevalue;
                    }
                } else {
                    $this->headerVars[$key] = $value;
                }
            }
        }
    }

    private function getUrlVars()
    {
        $this->urlVars = $_GET;
        foreach ($this->urlExcludeVars as $var) {
            unset($this->urlVars[$var]);
        }
        $this->urlVarsRAW = join('&', $this->urlVars);
    }

    private function getBodyVars()
    {
        if (isset($this->headerVars['Content-Type']) && $this->headerVars['Content-Type'] == 'application/json') {
            $this->bodyVarsRAW = file_get_contents("php://input");
            $this->bodyVars = json_decode($this->bodyVarsRAW, true);

        } elseif (isset($this->headerVars['Content-Type']) && $this->headerVars['Content-Type'] == 'application/x-www-form-urlencoded') {
            $this->bodyVarsRAW = file_get_contents("php://input");
            parse_str($this->bodyVarsRAW, $this->bodyVars);
        } elseif (isset($this->headerVars['Content-Type']) && $this->headerVars['Content-Type'] == 'multipart/form-data') {
            preg_match('/boundary=(.*)$/', $_SERVER['CONTENT_TYPE'], $matches);
            $boundary = $matches[1];
            $a_blocks = preg_split("/-+$boundary/", file_get_contents('php://input'));
            array_pop($a_blocks);
            foreach ($a_blocks as $id => $block) {
                if (empty($block)) {
                    continue;
                }
                if (strpos($block, 'application/octet-stream') !== false) {
                    preg_match("/name=\"([^\"]*)\".*stream[\n|\r]+([^\n\r].*)?$/s", $block, $matches);
                } else {
                    preg_match('/name=\"([^\"]*)\"[\n|\r]+([^\n\r].*)?\r$/s', $block, $matches);
                }
                $bodyVars[$matches[1]] = $matches[2];
            }
            $this->bodyVarsRAW = '';
            $this->bodyVars = $bodyVars;
        } else {
            $this->bodyVarsRAW = '';
            $this->bodyVars = [];
        }

    }

    private function getCookieVars()
    {
        foreach ($_COOKIE as $name => $value) {
            $this->cookieVars[$name] = $value;
        }

    }

    protected function resolveRequestUri()
    {
        if (isset($_SERVER['HTTP_X_REWRITE_URL'])) { // IIS
            $requestUri = $_SERVER['HTTP_X_REWRITE_URL'];
        } elseif (isset($_SERVER['REQUEST_URI'])) {
            $requestUri = $_SERVER['REQUEST_URI'];
            if ($requestUri !== '' && $requestUri[0] !== '/') {
                $requestUri = preg_replace('/^(http|https):\/\/[^\/]+/i', '', $requestUri);
            }
        } elseif (isset($_SERVER['ORIG_PATH_INFO'])) { // IIS 5.0 CGI
            $requestUri = $_SERVER['ORIG_PATH_INFO'];
            if (!empty($_SERVER['QUERY_STRING'])) {
                $requestUri .= '?' . $_SERVER['QUERY_STRING'];
            }
        } else {
            throw new InvalidConfigException('Unable to determine the request URI.');
        }

        return $requestUri;
    }

    public function getQueryString()
    {
        return isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '';
    }

    public function getIsSecureConnection()
    {
        return isset($_SERVER['HTTPS']) && (strcasecmp($_SERVER['HTTPS'], 'on') === 0 || $_SERVER['HTTPS'] == 1)
        || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strcasecmp($_SERVER['HTTP_X_FORWARDED_PROTO'], 'https') === 0;
    }

    public function getServerName()
    {
        return $_SERVER['SERVER_NAME'];
    }

    public function getServerPort()
    {
        return (int) $_SERVER['SERVER_PORT'];
    }

    public function getReferrer()
    {
        return isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null;
    }

    public function getUserAgent()
    {
        return isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null;
    }

    public function getUserIP()
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        if (isset($_SERVER['HTTP_CLIENT_IP']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR']) and preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s', $_SERVER['HTTP_X_FORWARDED_FOR'], $matches)) {
            foreach ($matches[0] as $xip) {
                if (!preg_match('#^(10|172\.16|192\.168)\.#', $xip)) {
                    $ip = $xip;
                    break;
                }
            }
        }
        return $ip;
    }

    public function getUserHost()
    {
        return isset($_SERVER['REMOTE_HOST']) ? $_SERVER['REMOTE_HOST'] : null;
    }

    public function getAuthUser()
    {
        return isset($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : null;
    }

    public function getAuthPassword()
    {
        return isset($_SERVER['PHP_AUTH_PW']) ? $_SERVER['PHP_AUTH_PW'] : null;
    }

    private $_port;

    public function getPort()
    {
        if ($this->_port === null) {
            $this->_port = !$this->getIsSecureConnection() && isset($_SERVER['SERVER_PORT']) ? (int) $_SERVER['SERVER_PORT'] : 80;
        }

        return $this->_port;
    }

    private $_securePort;

    public function getSecurePort()
    {
        if ($this->_securePort === null) {
            $this->_securePort = $this->getIsSecureConnection() && isset($_SERVER['SERVER_PORT']) ? (int) $_SERVER['SERVER_PORT'] : 443;
        }

        return $this->_securePort;
    }

    private $_contentTypes;

    public function getAcceptableContentTypes()
    {
        if ($this->_contentTypes === null) {
            if (isset($_SERVER['HTTP_ACCEPT'])) {
                $this->_contentTypes = $this->parseAcceptHeader($_SERVER['HTTP_ACCEPT']);
            } else {
                $this->_contentTypes = [];
            }
        }

        return $this->_contentTypes;
    }

    public function getContentType()
    {
        if (isset($_SERVER["CONTENT_TYPE"])) {
            return $_SERVER["CONTENT_TYPE"];
        } elseif (isset($_SERVER["HTTP_CONTENT_TYPE"])) {
            return $_SERVER["HTTP_CONTENT_TYPE"];
        }
        return null;
    }

    private $_languages;

    public function getAcceptableLanguages()
    {
        if ($this->_languages === null) {
            if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
                $this->_languages = array_keys($this->parseAcceptHeader($_SERVER['HTTP_ACCEPT_LANGUAGE']));
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
        if (isset($_SERVER['HTTP_IF_NONE_MATCH'])) {
            return preg_split('/[\s,]+/', str_replace('-gzip', '', $_SERVER['HTTP_IF_NONE_MATCH']), -1, PREG_SPLIT_NO_EMPTY);
        } else {
            return [];
        }
    }

}
