<?php
namespace common\office\type;

use ff;
use common\office\ExportBase;
use common\office\ExportBaseType;

class test  extends ExportBase implements ExportBaseType
{
    //设置文件内容
    public function content()
    {


        //获取请求参数
        // echo '<pre>';
        // var_dump( $this->vars );
        // echo '</pre>';
        // exit;
        
        $data = [
            ['title1' => '111', 'title2' => '222'],
            ['title1' => '111', 'title2' => '222'],
            ['title1' => '111', 'title2' => '222']
        ];
        $title = ['第一行标题', '第二行标题'];
     
        // Create new Spreadsheet object
        $this->spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $this->spreadsheet->getActiveSheet();
     
        // 方法一，使用 setCellValueByColumnAndRow
        //表头
        //设置单元格内容
        foreach ($title as $key => $value) {
            // 单元格内容写入
            $sheet->setCellValueByColumnAndRow($key + 1, 1, $value);
        }
        $row = 2; // 从第二行开始
        foreach ($data as $item) {
            $column = 1;
            foreach ($item as $value) {
                // 单元格内容写入
                $sheet->setCellValueByColumnAndRow($column, $row, $value);
                $column++;
            }
            $row++;
        }
     
        // // 方法二，使用 setCellValue
        // //表头
        // //设置单元格内容
        // $titCol = 'A';
        // foreach ($title as $key => $value) {
        //     // 单元格内容写入
        //     $sheet->setCellValue($titCol . '1', $value);
        //     $titCol++;
        // }
        // $row = 2; // 从第二行开始
        // foreach ($data as $item) {
        //     $dataCol = 'A';
        //     foreach ($item as $value) {
        //         // 单元格内容写入
        //         $sheet->setCellValue($dataCol . $row, $value);
        //         $dataCol++;
        //     }
        //     $row++;
        // }


         // 示例3，使用 setCellValue
        
        // $this->spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        // $sheet = $this->spreadsheet->getActiveSheet();
        // $sheet->setCellValue('A1', 'Hello World !');
    }

}
