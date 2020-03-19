<?php
namespace common\office;

use PhpOffice;

class Export extends ExportBase
{

    public $spreadsheet;

    public function loadContent($type = '')
    {

        $callContentClass = '\common\office\type\\' . $type;

        if (is_subclass_of($callContentClass, '\common\office\ExportBase')) {
            call_user_func(array((new $callContentClass), 'content'));
        } else {
            throw new \Exception("Export Type Content Class Error", 1);

        }

    }

    //文件方式保存
    public function file()
    {

        if (empty($this->fileName)) {
            $this->setFileName();
        }

        $saveDirPath = SYSTEM_RUNTIME_PATH . '/PhpOfficeExport';

        if (!file_exists($saveDirPath)) {
            mkdir($saveDirPath, 0777, true);
        }

        $file = '';

        if ($this->fileType == 'excel') {
            $file = $saveDirPath . '/' . $this->fileName . '.xlsx';
            $writer = new PhpOffice\PhpSpreadsheet\Writer\Xlsx($this->spreadsheet);
            $writer->save($file);
        }
        return $file;
    }

    //直接导出下载
    public function output()
    {

        if (empty($this->fileName)) {
            $this->setFileName();
        }

        // If you're serving to IE over SSL, then the following may be needed
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
        header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header('Pragma: public'); // HTTP/1.0
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');

        if ($this->fileType == 'excel') {
            // Redirect output to a client’s web browser (Xlsx)
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $this->fileName . '.xlsx"');

            $writer = PhpOffice\PhpSpreadsheet\IOFactory::createWriter($this->spreadsheet, 'Xlsx');

        }
        $writer->save('php://output');

    }
}
