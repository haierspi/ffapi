<?php
namespace common\office;

use ff;

class ExportBase
{
    //不含后缀文件名
    protected $fileName = '';
    //设置导出生成文件名
    protected $fileType = 'excel';
    public $spreadsheet;
    public $vars;
    protected $instance;
    protected $normalFields = [];
    protected $normalKeyFields = [];

    public function __construct($load = false)
    {

        if (get_called_class() != get_class()) {
            $this->instance = ff::createObject(__CLASS__);
            $this->spreadsheet = &$this->instance->spreadsheet;
            $this->fileName = &$this->instance->fileName;
            $this->fileType = &$this->instance->fileType;
        }

        $this->vars = ff::$app->router->request->vars;

    }

    //设置文件内容
    public function setFileName($fileName = null)
    {
        if ($fileName) {
            $this->instance->fileName = $fileName;
        } else {
            $this->instance->fileName = uniqid($this->fileType . "_");
        }
        return $this->instance->fileName;
    }

    //获取文件本地绝对地址
    public function getFilePath($fileName = null)
    {
        return SYSTEM_RUNTIME_PATH . '/PhpOfficeExport' . $this->instance->fileName;
    }

    public function setFileType($fileType)
    {
        if ($fileType) {
            $this->instance->fileType = $fileType;
        }
    }

    //常规导出 - 导入字段清单
    public function importNormalFields(array $fields = [])
    {
        $this->normalFields = $fields;

        $sheet = $this->spreadsheet->getActiveSheet();

        $column = 1;
        foreach ($fields as $field => $fieldValue) {
            // 单元格内容写入
            $sheet->setCellValueByColumnAndRow($column, 1, $fieldValue);
            $this->normalKeyFields[$column] = $field;
            $column++;
        }
    }

    public function normalBuild($buildData,$row = 2)
    {
        $sheet = $this->spreadsheet->getActiveSheet();
        //从第二行是数据
        foreach ($buildData as $item) {
            foreach ($this->normalKeyFields as $column => $field) {
                $sheet->setCellValueByColumnAndRow($column, $row, $item[$field]);
            }
            $row++;
        }
    }
}
