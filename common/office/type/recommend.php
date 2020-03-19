<?php
namespace common\office\type;

use ff;
use common\office\ExportBase;
use common\office\ExportBaseType;
use models\v1_0\Recommend as Recommends;
use models\v1_0\RecommendSizeDetails;
class recommend  extends ExportBase implements ExportBaseType
{
    public function content(){
        $rId=$this->vars['rId'];

        $info=Recommends::details($rId);

        $sizeDetails=RecommendSizeDetails::getSize($rId);

        // Create new Spreadsheet object
        $this->spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $this->spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1',  '货号');

        $sheet->mergeCells('B1:C1');
        $sheet->setCellValue('B1',  $info['goodsSn']);

        $sheet->setCellValue('D1',  '版单号');
        $sheet->mergeCells('E1:F1');
        $sheet->setCellValue('E1',  $info['rSn']);

        $sheet->setCellValue('G1',  '打版次数');
        $sheet->setCellValue('H1',  $info['goodsNumber']);
        $sheet->setCellValue('I1',  '审版不通过原因');
        $sheet->setCellValue('J1',  $info['examineFaildType']);

        $sheet->setCellValue('K1',  '业务跟单');
        $sheet->mergeCells('L1:M1');
        $sheet->setCellValue('L1',  $info['merchandiserNickname']);

        $sheet->mergeCells('A2:A4');
        $sheet->setCellValue('A2','打版注意事项');

        $sheet->mergeCells('B2:J4');
        $sheet->setCellValue('B2',$info['patternItem']);

        $sheet->mergeCells('A5:A6');
        $sheet->setCellValue('A5','样衣注意事项');

        $sheet->mergeCells('B5:J6');
        $sheet->setCellValue('B5',$info['sampleNotice']);

        $sheet->setCellValue('A7','部位');
        $sheet->setCellValue('B7','量法');
        $sheet->setCellValue('C7','档差');
        $sheet->setCellValue('D7','样衣尺寸');
        $sheet->setCellValue('E7','样衣大货码');
        $sheet->setCellValue('F7','S');
        $sheet->setCellValue('G7','M');
        $sheet->setCellValue('H7','L');
        $sheet->setCellValue('I7','XL');
        $sheet->setCellValue('J7','XXL');
        $sheet->setCellValue('K7','XXXL');
        $sheet->setCellValue('L7','XXXXL');
        $sheet->setCellValue('M7','XXXXXL');
        $sheet->setCellValue('N7','One-size');
        $sheet->getStyle('A7:N7')->getFont()->setBold(true);// 一定范围内字体加粗


        $i=8;
        foreach($sizeDetails as $key=>$val){
            $sheet->setCellValue('A'.$i,$val['name']);
            $sheet->setCellValue('B'.$i,$val['quanityMethod']);
            $sheet->setCellValue('C'.$i,$val['stallError']);
            $sheet->setCellValue('D'.$i,$val['SampleNumber']);
            $sheet->setCellValue('E'.$i,$val['SampleBigNumber']);
            $sheet->setCellValue('F'.$i,$val['S']);
            $sheet->setCellValue('G'.$i,$val['M']);
            $sheet->setCellValue('H'.$i,$val['L']);
            $sheet->setCellValue('I'.$i,$val['XL']);
            $sheet->setCellValue('J'.$i,$val['XXL']);
            $sheet->setCellValue('K'.$i,$val['XXXL']);
            $sheet->setCellValue('L'.$i,$val['XXXXL']);
            $sheet->setCellValue('M'.$i,$val['XXXXXL']);
            $sheet->setCellValue('N'.$i,$val['One-size']);
            $i++;
        }


    }
}