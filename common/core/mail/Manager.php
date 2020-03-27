<?php
namespace ff\mail;

use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;
use AlibabaCloud\Ecs\Ecs;
use ff;

class Manager
{
    public $config;
    private $mailer;
    private $type;

    /**
     * Undocumented function
     *
     * @param [type] $type
     * @Author HaierSpi haierspi@qq.com
     * @DateTime 2020-03-20
     */
    public function __construct($mixedObject = null)
    {
        //使用配置的类
        if (is_null($mixedObject)) {
            $this->mailer = ff::createObject(ff::$config['mail']['class']);
        } 
        //传入对象
        elseif (is_object($mixedObject)) {
            $this->mailer = $mixedObject;
        } 
        //传递进类名
        elseif (is_string($mixedObject)) {
            $this->mailer = ff::createObject($mixedObject);
        }

        if (!is_subclass_of($this->mailer, '\ff\mail\MailInterface')) {
            throw new \Exception("ERROR! Mail Class :" . ff::$config['mail']['class'] . " Must be an Interface (ff\mail\MailInterface) Implementation ");
        }

        $className = $this->getNameKey();
        
        $this->mailer->config(ff::$config['mail'][$className]);

    }

    public function send($toAddress, $subject, $content)
    {
        return $this->mailer->send($toAddress, $subject, $content);
    }

    public function getNameKey()
    {
        return basename(str_replace('\\', '/', get_class($this->mailer)));
    }


}
