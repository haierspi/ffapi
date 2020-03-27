<?php
namespace ff\upload;

use ff;

class Manager
{
    public $config;

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
            $this->uploader = ff::createObject(ff::$config['upload']['class']);
        }
        //传入对象
        elseif (is_object($mixedObject)) {
            $this->uploader = $mixedObject;
        }
        //传递进类名
        elseif (is_string($mixedObject)) {
            $this->uploader = ff::createObject($mixedObject);
        }

        if (!is_subclass_of($this->uploader, '\ff\upload\UploadInterface')) {
            throw new \Exception("ERROR! Upload Class :" . ff::$config['mail']['class'] . " Must be an Interface (ff\upload\UploadInterface) Implementation ");
        }

        $className = $this->getNameKey();

        $this->uploader->config(ff::$config['upload'][$className]);

    }

    public function uploadByConent($fileContent, $fileName, $type = 'upload')
    {
        return $this->uploader->uploadByConent($fileContent, $fileName, $type);
    }
    public function uploadByFile($file, $fileName, $type = 'upload')
    {
        return $this->uploader->uploadByFile($file, $fileName, $type);
    }

    public function getNameKey()
    {
        return basename(str_replace('\\', '/', get_class($this->uploader)));
    }

    public function getBucket()
    {
        return $this->uploader->bucket;
    }

    public function getFileMimeType()
    {
        return $this->uploader->fileMimeType;
    }


}
