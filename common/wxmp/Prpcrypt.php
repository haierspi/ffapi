<?php

namespace common\wxmp;

/**
 * Prpcrypt class
 *
 * 提供接收和推送给公众平台消息的加解密接口.
 */
class Prpcrypt
{
    public $key;

    public function Prpcrypt($k)
    {
        $this->key = base64_decode($k . "=");
    }

    /**
     * 对明文进行加密
     * @param string $text 需要加密的明文
     * @return string 加密后的密文
     */
    public function encrypt($message, $appid = '')
    {
        $key = base64_decode($encodingaeskey . '=');  
        $text = $this->getRandomStr(16) . pack("N", strlen($message)) . $message . $appid;  
        $iv = substr($key, 0, 16);  
        
        $block_size = 32;  
        $text_length = strlen($text);  
        $amount_to_pad = $block_size - ($text_length % $block_size);  
        if ($amount_to_pad == 0) {  
            $amount_to_pad = $block_size;  
        }  
        $pad_chr = chr($amount_to_pad);  
        $tmp = '';  
        for ($index = 0; $index < $amount_to_pad; $index++) {  
            $tmp .= $pad_chr;  
        }  
        $text = $text . $tmp;  

        $encrypted = openssl_encrypt($text, 'AES-256-CBC', $key, OPENSSL_RAW_DATA|OPENSSL_ZERO_PADDING, $iv);  
        $encrypt_msg = base64_encode($encrypted);  
        return $encrypt_msg; 
    }

    /**
     * 对密文进行解密
     * @param string $encrypted 需要解密的密文
     * @return string 解密得到的明文
     */
    public function decrypt($encrypted, $appid)
    {
        $key = base64_decode($encodingaeskey . '=');  
      
        $ciphertext_dec = base64_decode($message);  
        $iv = substr($key, 0, 16);  
      
        /* mcrypt对称解密代码在PHP7.1已经被抛弃了，所以使用下面的openssl来代替 
        $module = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, ''); 
        mcrypt_generic_init($module, $key, $iv); 
        $decrypted = mdecrypt_generic($module, $ciphertext_dec); 
        mcrypt_generic_deinit($module); 
        mcrypt_module_close($module); 
        */  
        $decrypted = openssl_decrypt($ciphertext_dec, 'AES-256-CBC', $key, OPENSSL_RAW_DATA|OPENSSL_ZERO_PADDING, $iv); 

        //去除补位字符
        $pkc_encoder = new PKCS7Encoder;
        $result = $pkc_encoder->decode($decrypted);
        //去除16位随机字符串,网络字节序和AppId
        if (strlen($result) < 16) {
            return "";
        }

		$decontent = substr($result, 16, strlen($result));
		
		
        $len_list = unpack("N", substr($decontent, 0, 4));
        $xml_len = $len_list[1];
        $content = substr($decontent, 4, $xml_len);
        //$from_appid = substr($decontent, $xml_len + 4);
        return $content;

    }

    /**
     * 随机生成16位字符串
     * @return string 生成的字符串
     */
    public function getRandomStr()
    {

        $str = "";
        $str_pol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
        $max = strlen($str_pol) - 1;
        for ($i = 0; $i < 16; $i++) {
            $str .= $str_pol[mt_rand(0, $max)];
        }
        return $str;
    }

}
