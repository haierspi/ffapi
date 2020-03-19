<?php

namespace ff\network;

class httpClient
{
    public static function __callStatic($name, $arguments)
    {
        return self::build($name, $arguments[0], $arguments[1]);
    }

    private function build($method = 'GET', $url, $params, $header = array(), $timeout = 5, $formatparser = 'jsonparser')
    {
        // POST 提交方式的传入 $set_params 必须是字符串形式
        $opts = array(
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTPHEADER => $header,
        );

        /* 根据请求类型设置特定参数 */
        switch (strtoupper($method)) {
            case 'GET':
                $opts[CURLOPT_URL] = $url . '?' . http_build_query($params);
                break;
            case 'POST':
                $opts[CURLOPT_URL] = $url;
                $opts[CURLOPT_POST] = 1;
                $opts[CURLOPT_HTTPHEADER] = array('Content-type:application/json');
                $opts[CURLOPT_POSTFIELDS] = json_encode($params);
                break;
            case 'DELETE':
                $opts[CURLOPT_URL] = $url;
                $opts[CURLOPT_HTTPHEADER] = array("X-HTTP-Method-Override: DELETE");
                $opts[CURLOPT_CUSTOMREQUEST] = 'DELETE';
                $opts[CURLOPT_POSTFIELDS] = $params;
                break;
            case 'PUT':
                $opts[CURLOPT_URL] = $url;
                $opts[CURLOPT_POST] = 0;
                $opts[CURLOPT_CUSTOMREQUEST] = 'PUT';
                $opts[CURLOPT_HTTPHEADER] = array('Content-type:application/json');
                $opts[CURLOPT_POSTFIELDS] = json_encode($params);
                break;
            default:
                throw new Exception('不支持的请求方式！');
        }

        /* 初始化并执行curl请求 */
        $ch = curl_init();
        curl_setopt_array($ch, $opts);
        $data = curl_exec($ch);
        $error = curl_error($ch);

        return SELF::$formatparser($data);
    }

    private function jsonparse()
    {
        return json_decode($data, true);
    }

}
