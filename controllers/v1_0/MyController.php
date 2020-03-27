<?php
namespace controllers\v1_0;

use Aws\S3\S3Client;
use common\ErrorCode;
use common\SussedCode;
use DB;
use ff;
use ff\auth\TokenAuthController;
use models\v1_0\omsUser;

/**
 *
 * @name 用户中心接口 TOKEN
 *
 */

class myController extends TokenAuthController
{

    /**
     *
     * @name  获取用户信息
     * @method GET
     * @format JSON
     * @param string token yes 用户token
     * @var int status 状态码 (成功 1 ;失败 0;)
     * @var string msg 状态信息
     * @var object userData 用户信息
     * @other
     * @example
     * [success]JSON:{"code":1,"userData":{"uid":"1","user_type":"1","nickname":"\u8d85\u7ea7\u7ba1\u7406\u5458","username":"admin","email":"123123@qq.com","email_bind":"0","mobile":"13311502563","mobile_bind":"0","avatar":"0","score":"0","money":"0.00","reg_ip":"0","reg_type":"","create_time":"1438651748","update_time":"1566791266","status":"1","short":"AD"},"request_params":{"token":"UD7Qj7fW2ZeDypXTp7bR9K4+UgEBWlEBCQFUDQ==fad94e714d"},"request_dateline":1568172898.365951,"response_dateline":1568172898.515812}
     * @author haierspi
     *
     */
    public function actionPersonal($method = 'GET')
    {
        $omsUserModel = new omsUser();
        list($userData) = $omsUserModel->getUserByField($this->user->uid, 'id');

        return SussedCode::SUSSED([
            'userData' => $userData,
        ]);

    }

    /**
     *
     * @name  用户公共上传接口
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
    public function actionUpload($method = 'POST')
    {
        $type = $this->request->vars['type'];
        $file = $this->request->fileVars['file'];

        if (empty($type) || empty($file)) {
            return ErrorCode::MISSING_PARAMETER(); //缺少参数
        }

        if (!preg_match('/^[\w]*$/is', $type)) {
            return ErrorCode::PARAMETER_ERROR(); //参数错误
        }
        //获取文件类型

        //获取上传文件后缀名
        $fileExt = substr($file['name'], strrpos($file['name'], '.') + 1);

        $fileContent = file_get_contents($file['tmp_name']);

        $upload = new \ff\upload\Manager();

        $fileURL = $upload->uploadByFile(
            $file['tmp_name'], $file['name'], 'file_export'
        );

        $bucket = $upload->getBucket();

        $fileMimeType = $upload->getFileMimeType();

        $data = [
            'name' => $file['name'],
            'ext' => $fileExt,
            'size' => $file['size'],
            'provider' => 'aws_s3',
            'bucket' => $bucket,
            'type' => strtolower($type),
            'mimeType' => $fileMimeType,
            'url' => $fileURL,
            'uid' => $this->user->uid,
            'ip' => $this->request->clientip,
            'datetime' => date('Y-m-d H:i:s'),
        ];

        $data['fileId'] = DB::table('file_upload')->insertGetId($data);

        return SussedCode::SUSSED([
            'fileId' => $data['fileId'], 'fileData' => $data, 'fileURL' => $fileURL,
        ]);

    }

}
