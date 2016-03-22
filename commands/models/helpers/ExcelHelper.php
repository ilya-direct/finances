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

    public function checkHeaders($headers)
    {
        $headerRow = \yii::$app->params['headerRow'];
        foreach ($headers as $h) {
            switch ($h->name) {
                case 'note':
                    $cellValue = self::cellValue($this->getCell($h->letter, 7));
                    break;
                case 'realmoney':
                    $cellValue = self::cellValue($this->getCell($h->letter, 4));
                    break;
                case 'correcting':
                    $cellValue = self::cellValue($this->getCell($h->letter, 4));
                    break;
                default:
                    $cellValue = self::cellValue($this->getCell($h->letter, $headerRow));
            }
            if ($h->value != $cellValue) {
                if(!($h->value=='Корректировка' && $cellValue=="Разница")){
                    throw new \Exception("Unexpected header : {$cellValue}. {$h->value} expected");
                }
            }
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

    /**
     * @param $cell \PHPExcel_Cell
     * @return \PHPExcel_Cell
     * @throws \PHPExcel_Exception
     */

    static function cellValue($cell){
        $val = $cell->getValue();
        if(\PHPExcel_Shared_Date::isDateTime($cell)) {
            $val = date('d.m.Y', \PHPExcel_Shared_Date::ExcelToPHP($val));
        }
        if((substr($val,0,1) === '=' ) && (strlen($val) > 1)){
            $val = $cell->getOldCalculatedValue();
        }

        return $val;
    }



}