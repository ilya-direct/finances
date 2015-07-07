<?php

namespace app\commands;

class WalletController extends \yii\console\Controller
{
    public function actionIndex()
    {
	    $DIR=dirname(__FILE__);
	    $objPHPExcel = \PHPExcel_IOFactory::load($DIR. '/2014.10.xlsm');
	    $sheetData = $objPHPExcel->getActiveSheet()->toArray(null, false, true, true);
	    print_r($sheetData);
    }

}
