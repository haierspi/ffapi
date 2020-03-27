<?php
namespace ff\upload;

class AWS implements UploadInterface
{
    public $config;
    private $mimeTypes = [];
    public $bucket;
    public $fileMimeType;
    

    

    /**
     * 通过文件内容上传到AWS
     *
     * @param [type] $fileContent
     * @param [type] $fileName
     * @param string $type
     * @return void
     * @Author HaierSpi haierspi@qq.com
     * @DateTime 2020-03-20
     */
    public function uploadByConent($fileContent, $fileName, $type)
    {

        $this->getMimeTypes();
        //获取上传文件后缀名

        $fileExt = pathinfo($fileName)['extension'];

        $s3Client = new \Aws\S3\S3Client([
            'region' => $this->config['s3']['region'],
            //'region' => 'us-east-1',
            'version' => '2006-03-01',
            'credentials' => [
                'key' => $this->config['access_key_id'],
                'secret' => $this->config['secret_access_key'],
            ],
        ]);

        $this->bucket = $this->config['s3']['bucket'];
        $this->fileMimeType = $this->mimeTypes[$fileExt];

        //获取文件名
        $fileKey = strtolower($type) . '/' . date('Y-m-d') . '/' . uniqid() . ($fileExt ? '.' . $fileExt : '');

        try {
            $result = $s3Client->putObject([
                'Bucket' => $this->bucket,
                'Key' => $fileKey,
                'Body' => $fileContent,
                'ContentType' => $this->fileMimeType,
                'ACL' => 'public-read',
            ]);
        } catch (\InvalidArgumentException $e) {

            throw new \Exception("AWS InvalidArgumentException ERROR!" . PHP_EOL .
                $exception->getMessage()
            );

        } catch (\Aws\S3\Exception\S3Exception $e) {

            throw new \Exception("Aws S3Exception ERROR!" . PHP_EOL .
                $e->getMessage()
            );
        }
        return $result->get('ObjectURL');
    }

    /**
     * 通过文件上传到AWS
     *
     * @param [type] $file
     * @param string $type
     * @return void
     * @Author HaierSpi haierspi@qq.com
     * @DateTime 2020-03-20
     */
    public function uploadByFile($file, $fileName, $type)
    {

        //获取上传文件后缀名

        $fileContent = file_get_contents($file);

        return $this->uploadByConent($fileContent, $fileName, $type);

    }

    public function getMimeTypes()
    {
        $this->mimeTypes = require SYSTEM_ROOT_PATH . '/data/mimeTypes.php';
    }

    public function config($config)
    {
        $this->config = $config;
    }

}
