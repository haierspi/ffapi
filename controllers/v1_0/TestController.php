<?php
namespace controllers\v1_0;

use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;
use AlibabaCloud\Ecs\Ecs;
use common\ErrorCode;
use common\SussedCode;
use ff;
use ff\auth\TokenAuthController;
use ff\base\Controller;
use ff\database\db;
use ff\database\Model;
use ff\database\Schema;
use ff\database\UserModel;
use ff\nosql\redis;
use models\v1_0\TestModel2;
use models\v1_0\TestModel;
use models\v1_0\testSqlsrv;

/**
 *
 * @name 测试相关接口
 *
 */

class testController extends Controller
{
    /**
     *
     * @name  获取token用户UID两种方式
     * @method GET
     * @format JSON
     * @param string token yes 用户token
     * @var int status 状态码 (成功 1 ;失败 0;)
     * @var string msg 状态信息
     * @other
     * @example
     * [success]JSON:{}
     * @author haierspi
     *
     */
    public function actionTokenUid($method = 'GET')
    {
        // case 1
        $uid1 = $this->user->uid;

        // case 2
        $userModel = new userModel;
        $uid2 = $userModel->uid;

        return ['code' => 1, 'uid1' => $uid1, 'uid2' => $uid2];

    }

    /**
     * @name 获取环境信息
     * @method POST
     * @format JSON
     * @param  string  title yes 商品名称
     * @param  string  page no 显示页数 默认显示第一页
     * @param  string  pagenum no 每页显示数量  默认每页显示10个
     * @var int code 状态码(成功1 ;失败0)
     * @var string msg 状态信息
     * @var array_object goodses 商品列表
     * @other
     * @example
     * [GET][SUCCESS]JSON:
     *
     * @author Wind-dust
     *
     */

    public function actionRunTime($method = 'GET|CLI')
    {

        echo '<pre>';
        var_dump(ff::$config);
        echo '</pre>';
        exit;

        //打印SQL
        $dasql = DB::getQueryLog();

        foreach ($dasql as $one) {
            $sql[] = vsprintf(str_replace('?', "'%s'", $one['query']), $one['bindings']);
        }

        $RUNTIME_ENVIROMENT = constant('RUNTIME_ENVIROMENT');
        return ['code' => 1, 'RUNTIME_ENVIROMENT' => $RUNTIME_ENVIROMENT];
    }

    /**
     * @name CLI下判断任务是否正在执行
     * @method POST
     * @format JSON
     * @param  string  title yes 商品名称
     * @param  string  page no 显示页数 默认显示第一页
     * @param  string  pagenum no 每页显示数量  默认每页显示10个
     * @var int code 状态码(成功1 ;失败0)
     * @var string msg 状态信息
     * @var array_object goodses 商品列表
     * @other
     * @example
     * [GET][SUCCESS]JSON:
     *
     * @author Wind-dust
     *
     */

    public function actionOneTaskOneTime($method = 'GET|CLI')
    {

        //在控制器内判断当前控制器是否有一个任务正在执行中
        if (\ff\os\System::progressIsExistsByControllerAction()) {
            // do
        }
        //指定某一个控制器是否有一个任务正在执行中
        if (\ff\os\System::progressIsExists('v1.0/ScmDIService/DIGoodsSkuAnalys')) {
            // do
        }

        /*

    progressIsExistsByControllerAction 和 progressIsExists  后面的参数 设置是否包含自身..

     */

    }

    public function actionErrorCode($method = 'GET|CLI')
    {

        echo '<pre>';
        var_dump(ErrorCode::ACCESS_DENIED(['do' => 'yes']));
        echo '</pre>';
        exit;

        return ErrorCode::ACCESS_DENIED(['do' => 'yes']);

    }

    /**
     * @name 测试REDIS
     * @method GET
     * @format JSON
     * @param  string  title yes 商品名称
     * @param  string  page no 显示页数 默认显示第一页
     * @param  string  pagenum no 每页显示数量  默认每页显示10个
     * @var int code 状态码(成功1 ;失败0)
     * @var string msg 状态信息
     * @var array_object goodses 商品列表
     * @other
     * @example
     * [GET][SUCCESS]JSON:
     *
     * @author Wind-dust
     *
     */

    public function actionRedis($method = 'GET')
    {

        redis::set('testkey', '111111');

        echo '<pre>';
        var_dump(redis::get('testkey'));
        echo '</pre>';
        exit;

        $RUNTIME_ENVIROMENT = constant('RUNTIME_ENVIROMENT');
        return ['code' => 1, 'RUNTIME_ENVIROMENT' => $RUNTIME_ENVIROMENT];
    }

    /**
     * @name 测试Fb回调
     * @method GET
     * @format JSON
     * @param  string  title yes 商品名称
     * @param  string  page no 显示页数 默认显示第一页
     * @param  string  pagenum no 每页显示数量  默认每页显示10个
     * @var int code 状态码(成功1 ;失败0)
     * @var string msg 状态信息
     * @var array_object goodses 商品列表
     * @other
     * @example
     * [GET][SUCCESS]JSON:
     *
     * @author Wind-dust
     *
     */

    public function actionFbtest($method = 'GET')
    {

        $challenge = $_REQUEST['hub_challenge'];
        $verify_token = $_REQUEST['hub_verify_token'];

        if ($verify_token === 'token_my_token') {
            echo $challenge;
        }
        exit;

    }

    /**
     * @name 测试sqlsrv
     * @method GET
     * @format JSON
     * @param  string  title yes 商品名称
     * @param  string  page no 显示页数 默认显示第一页
     * @param  string  pagenum no 每页显示数量  默认每页显示10个
     * @var int code 状态码(成功1 ;失败0)
     * @var string msg 状态信息
     * @var array_object goodses 商品列表
     * @other
     * @example
     * [GET][SUCCESS]JSON:
     *
     * @author Wind-dust
     *
     */

    public function actionSqlsrv($method = 'GET')
    {

        $x = new testSqlsrv();

        echo '<pre>';
        var_dump($x->getBalanceID());
        echo '</pre>';
        exit;

        $RUNTIME_ENVIROMENT = constant('RUNTIME_ENVIROMENT');
        return ['code' => 1, 'RUNTIME_ENVIROMENT' => $RUNTIME_ENVIROMENT];
    }

    /**
     * @name 测试Schema
     * @method GET
     * @format JSON
     * @var int code 状态码(成功1 ;失败0)
     * @var string msg 状态信息
     * @var array_object goodses 商品列表
     * @other
     * @example
     * [GET][SUCCESS]JSON:
     *
     * @author Wind-dust
     *
     */

    public function actionSchema($method = 'GET')
    {

        echo '<pre>';
        var_dump(\ff\database\Schema::hasTable('scm'));
        echo '</pre>';
        exit;

    }

    /**
     * @name 获取数据库字段
     * @method GET
     * @var int code 状态码(成功1 ;失败0)
     * @var string msg 状态信息
     * @var array_object goodses 商品列表
     * @other
     * @example
     * [GET][SUCCESS]JSON:
     *
     * @author Wind-dust
     *
     */

    public function actionDBSchema($method = 'GET')
    {
        $columns = \ff\database\Schema::getColumnListing('users');
        dd($columns);

    }

    /**
     *
     * @name  AWS S3 公共上传TEST接口
     * @method POST
     * @format JSON
     * @param file file yes 上传文件变量 详情参考 POST form-data
     * @param string type yes 文件用途类型 例如 用户头像上传 可以指定 user_avatar 这个参数 必须为 字母 数字或下横杠组成
     * @var int status 状态码 (成功 1 ;失败 0;)
     * @var string msg 状态信息
     * @var object fileId 上传文件ID
     * @var object fileURL 上传文件的对外地址
     * @var object fileData 上传文件数据
     * @other
     * @example
     * [success]JSON:{}
     * @author haierspi
     *
     */
    public function actionAwsUploadTest($method = 'GET')
    {

        $type = 'devtest';
        //获取文件类型
        $mimeTypes = require SYSTEM_ROOT_PATH . '/data/mimeTypes.php';
        //获取上传文件后缀名

        $file = constant('SYSTEM_ROOT_PATH') . '/run/static/image/linktarget.png';

        $fileExt = 'png';

        $fileContent = file_get_contents($file);

        $s3Client = new \Aws\S3\S3Client([
            'region' => ff::$config['aws']['s3']['region'],
            //'region' => 'us-east-1',
            'version' => '2006-03-01',
            'credentials' => [
                'key' => ff::$config['aws']['access_key_id'],
                'secret' => ff::$config['aws']['secret_access_key'],
            ],
        ]);

        $bucket = ff::$config['aws']['s3']['bucket'];

        //获取文件名
        $fileKey = strtolower($type) . '/' . date('Y-m-d') . '/' . uniqid() . '.' . $fileExt;

        try {
            $result = $s3Client->putObject([
                'Bucket' => $bucket,
                'Key' => $fileKey,
                'Body' => $fileContent,
                'ContentType' => $mimeTypes[$fileExt],
                'ACL' => 'public-read',
            ]);
        } catch (\InvalidArgumentException $e) {
            return ['code' => 0, 'msg' => 'InvalidArgumentException', 'error' => $e->getMessage()];
        } catch (\Aws\S3\Exception\S3Exception $e) {
            return ['code' => 0, 'msg' => 'S3Exception', 'error' => $e->getMessage()];
        }

        $fileURL = $result->get('ObjectURL');

        return ['code' => 1, 'msg' => 'suss', 'fileURL' => $fileURL];

    }

    /**
     *
     * @name  Mail邮件发送测试
     * @method POST
     * @format JSON
     * @param file file yes 上传文件变量 详情参考 POST form-data
     * @param string type yes 文件用途类型 例如 用户头像上传 可以指定 user_avatar 这个参数 必须为 字母 数字或下横杠组成
     * @var int status 状态码 (成功 1 ;失败 0;)
     * @var string msg 状态信息
     * @var object fileId 上传文件ID
     * @var object fileURL 上传文件的对外地址
     * @var object fileData 上传文件数据
     * @other
     * @example
     * [success]JSON:{}
     * @author haierspi
     *
     */
    public function actionMail($method = 'GET')
    {

        AlibabaCloud::accessKeyClient(
            'LTAI4Fv9b2EZbHbwkAFoYGGA', //ACCESS_KEY_ID
            'C0xUZTbQhU8HW4UWwrQLJAClRP7tsg' //ACCESS_KEY_SECRET
        )
            ->regionId('cn-hangzhou')
            ->asDefaultClient();

        try {

            $result = AlibabaCloud::dm()
                ->V20151123()
                ->SingleSendMail()
                ->withAccountName("admin@sendmail.lwhs.me")
                ->withFromAlias("品腾科技-服务系统")
                ->withAddressType(1)
            //->withTagName("控制台创建的标签")
                ->withReplyToAddress("true")
                ->withToAddress("haierspi@qq.com")
                ->withSubject("OMS和浦沅2012-11-03邮件推送")
                ->withHtmlBody("邮件正文")
                ->request();

            echo '<pre>';
            var_dump($result->toArray());
            echo '</pre>';
            exit;

            // $request = new \AlibabaCloud\Dm\V20151123\SingleSendMailRequest();

        } catch (ClientException $exception) {
            echo $exception->getMessage() . PHP_EOL;
        } catch (ServerException $exception) {
            echo $exception->getMessage() . PHP_EOL;
            echo $exception->getErrorCode() . PHP_EOL;
            echo $exception->getRequestId() . PHP_EOL;
            echo $exception->getErrorMessage() . PHP_EOL;

        }

        return ['code' => 1, 'msg' => 'suss', 'fileURL' => $fileURL];

    }

}
