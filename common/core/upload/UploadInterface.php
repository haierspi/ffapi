<?php
namespace ff\upload;

interface UploadInterface
{
    public function uploadByConent($fileContent, $fileName, $type );
    public function uploadByFile($file, $fileName, $type);
    public function config($config);
}
