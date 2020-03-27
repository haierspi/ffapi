<?php
namespace ff\mail;

use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;
use AlibabaCloud\Ecs\Ecs;

class AlibabaCloudDM implements MailInterface
{

    public $config;

    /**
     * 文件方式保存
     *
     * @param [type] $withToAddress
     * @param [type] $subject
     * @param [type] $content
     * @return void
     * @Author HaierSpi haierspi@qq.com
     * @DateTime 2020-03-20
     */
    public function send($withToAddress, $subject, $content)
    {
        AlibabaCloud::accessKeyClient(
            $this->config['access_key_id'], //ACCESS_KEY_ID
            $this->config['secret_access_key']//ACCESS_KEY_SECRET
        )
            ->regionId($this->config['regionId'])
            ->asDefaultClient();

        try {

            $result = AlibabaCloud::dm()
                ->V20151123()
                ->SingleSendMail()
                ->withAccountName($this->config['withAccountName'])
                ->withFromAlias($this->config['withFromAlias'])
                ->withAddressType(1)
                ->withReplyToAddress("true")
                ->withToAddress($withToAddress)
                ->withSubject($subject)
                ->withHtmlBody($content)
                ->request();

        } catch (ClientException $exception) {
            throw new \Exception("ClientException ERROR!" . PHP_EOL .
                $exception->getMessage()
            );
        } catch (ServerException $exception) {
            throw new \Exception("ServerException ERROR!" . PHP_EOL .
                $exception->getMessage() . PHP_EOL .
                $exception->getErrorCode() . PHP_EOL .
                $exception->getRequestId() . PHP_EOL .
                $exception->getErrorMessage() . PHP_EOL
            );

        }

        return $result->toArray();

    }

    public function config($config)
    {
        $this->config = $config;
    }

}
