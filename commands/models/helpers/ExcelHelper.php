<?php

namespace app\commands\models\helpers;


use app\models\Finance2016\XlsxColumn;

class ExcelHelper
{
    /**
     * @var null | \PHPExcel_Worksheet
     */
    private  $_sheet=null;

    public function __construct($sheet){
        $this->_sheet=$sheet;
    }

    public function checkHeaders($headers){
        $headerRow=\yii::$app->params['headerRow'];
        foreach($headers as $h){
            switch($h->name){
                case 'note':
                    $cellValue=$this->getCellValue($h->letter,7);
                    break;
                case 'realmoney':
                    $cellValue=$this->getCellValue($h->letter,4);
                    break;
                default:
                    $cellValue=$this->getCellValue($h->letter,$headerRow);
            }
            if( $h->value != $cellValue )
                throw new \Exception("Unexpected header : {$cellValue}. {$h->value} expected");
        }
    }

    public function getCellValue($column,$row){
        return $this->getCell($column,$row)->getFormattedValue();
    }

    public function getCell($column,$row){
        return   $this->_sheet->getCell($column.$row);
    }

    /**
     * @param $cell \PHPExcel_Cell
     * @return \PHPExcel_Cell
     */

    public function nextCell($column,$row){
        $column=\PHPExcel_Cell::columnIndexFromString($column);
        return   $this->_sheet->getCellByColumnAndRow($column,$row);
    }

    /**
     * @param $cell \PHPExcel_Cell
     * @return \PHPExcel_Cell
     * @throws \PHPExcel_Exception
     */

    public function prevCell($column,$row){
        $column=\PHPExcel_Cell::columnIndexFromString($column)-2;
        return   $this->_sheet->getCellByColumnAndRow($column,$row);
    }



}