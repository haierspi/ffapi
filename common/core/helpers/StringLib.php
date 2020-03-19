<?php
namespace ff\helpers;

class StringLib
{
    //获取随机字符串
    public static function randString($length = 5, $mod = 'UMN', $starthash = '')
    {
        $hash = $starthash;

        if (preg_match("/U/i", $mod)) {
            $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        }
        if (preg_match("/M/i", $mod)) {
            $chars .= 'abcdefghijklmnopqrstuvwxyz';
        }
        if (preg_match("/N/i", $mod)) {
            $chars .= '0123456789';
        }
        $max = strlen($chars) - 1;
        if (PHP_VERSION < '4.2.0') {
            mt_srand((double) microtime() * 1000000);
        }
        for ($i = 0; $i < $length; $i++) {

            $hash .= $chars[mt_rand(0, $max)];
            if ($i == 0) {
                $hash = ($hash[0] == '0') ? '1' : $hash;
            }
        }
        return $hash;
    }

    //双向加密函数
    public static function myEncrypt($string, $action = 'EN', $auth = '')
    {
        $string = strval($string);
        if ($string == '') {
            return '';
        }

        if ($action == 'EN') {
            $strauth = substr(md5($string), 8, 10);
        } else {
            $strauth = substr($string, -10);
            $string = base64_decode(substr($string, 0, strlen($string) - 10));
        }
        $key = md5($strauth . $auth);
        $len = strlen($key);
        $code = '';
        for ($i = 0; $i < strlen($string); $i++) {
            $k = $i % $len;
            $code .= $string[$i] ^ $key[$k];
        }
        $code = ($action == 'DE' ? (substr(md5($code), 8, 10) == $strauth ? $code : null) : base64_encode($code) . $strauth);
        return $code;
    }

    //认证
    public static function getArySign($array, $authkey = 'bigqi_com', $authmode = 'md5')
    {
        global $_SIGNSTR;
        if (is_array($array)) {
            if ($array['sign']) {
                unset($array['sign'], $array['sign_type']);
            }
            ksort($array);
            reset($array);
            $sign = '';
            foreach ($array as $key => $value) {
                if ($value != '') {
                    $sign .= "$key=$value&";
                }
            }
            $_SIGNSTR = substr($sign, 0, -1);
            $sign = substr($sign, 0, -1) . $authkey;

            $authmodeary = array('md5', 'num6');

            $authmode = in_array($authmode, $authmodeary) ? $authmode : current($authmodeary);

            if ($authmode == 'md5') {
                return md5($sign);
            } elseif ($authmode == 'num6') {
                return substr(sprintf("%u", crc32($sign)), 0, 6);
            }

        } else {
            return false;
        }
    }

    //根据字符串生成颜色代码
    public static function strColor($str)
    {
        $strcode = md5($str);
        $hexcode = '';
        for ($i = 0; $i < strlen($strcode); $i++) {
            $hexcode .= base_convert($strcode[$i], 36, 16);
        }
        $colorhexcode = substr($hexcode, 0, 6);
        return '#' . $colorhexcode;
    }

    //获取文件大小
    public static function strBytes($val)
    {
        $val = trim($val);
        $last = strtolower($val{strlen($val) - 1});
        switch ($last) {
            case 'g':$val *= 1024;
            case 'm':$val *= 1024;
            case 'k':$val *= 1024;
        }
        return $val;
    }

    //获取网络地址内容
    public static function getUrlConent($url, $post = [], $cookie = [],$httpheader=[], &$headers = [], $limit = 0, $ip = '', $timeout = 15, $encodetype = 'URLENCODE')
    {

        $return = '';
        $matches = parse_url($url);
        $scheme = $matches['scheme'];
        $host = $matches['host'];
        $path = $matches['path'] ? $matches['path'] . ($matches['query'] ? '?' . $matches['query'] : '') : '/';
        $port = !empty($matches['port']) ? $matches['port'] : ($scheme == 'http' ? '80' : '');


        $ch = curl_init();
        //$httpheader = array();


        
        if ($ip) {
            $httpheader[] = "Host: " . $host;
        }
        if ($httpheader) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $httpheader);
        }
        curl_setopt($ch, CURLOPT_URL, $scheme . '://' . ($ip ? $ip : $host) . ($port ? ':' . $port : '') . $path);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        if ($post) {
            curl_setopt($ch, CURLOPT_POST, 1);
            if ($encodetype == 'JSON') {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode( $post));
            }elseif ($encodetype == 'URLENCODE') {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
            } else {
                foreach ($post as $k => $v) {
                    if (isset($files[$k])) {
                        $post[$k] = '@' . $files[$k];
                    }
                }
                foreach ($files as $k => $file) {
                    if (!isset($post[$k]) && file_exists($file)) {
                        $post[$k] = '@' . $file;
                    }
                }
                curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
            }
        }
        if ($cookie) {
            foreach ($cookie as $key => $value) {
                $cookiestr .= "$key=" . urlencode($value) . ";";
            }
            $cookiestr = substr($cookiestr, 0, -1);
            curl_setopt($ch, CURLOPT_COOKIE, $cookiestr);
        }
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        $data = curl_exec($ch);
        $status = curl_getinfo($ch);
        $errno = curl_errno($ch);
        curl_close($ch);
        if ($errno || $status['http_code'] != 200) {
            return;
        } else {
            $headers = substr($data, 0, $status['header_size']);
            $data = substr($data, $status['header_size']);
            return !$limit ? $data : substr($data, 0, $limit);
        }
    }
    /**
     * Returns the trailing name component of a path.
     * This method is similar to the php function `basename()` except that it will
     * treat both \ and / as directory separators, independent of the operating system.
     * This method was mainly created to work on php namespaces. When working with real
     * file paths, php's `basename()` should work fine for you.
     * Note: this method is not aware of the actual filesystem, or path components such as "..".
     *
     * @param string $path A path string.
     * @param string $suffix If the name component ends in suffix this will also be cut off.
     * @return string the trailing name component of the given path.
     * @see http://www.php.net/manual/en/function.basename.php
     */
    public static function basename($path, $suffix = '')
    {
        if (($len = mb_strlen($suffix)) > 0 && mb_substr($path, -$len) === $suffix) {
            $path = mb_substr($path, 0, -$len);
        }
        $path = rtrim(str_replace('\\', '/', $path), '/\\');
        if (($pos = mb_strrpos($path, '/')) !== false) {
            return mb_substr($path, $pos + 1);
        }

        return $path;
    }


}
