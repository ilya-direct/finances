<?php
namespace app\commands;

use yii;
use yii\console\Controller;
use app\models\CompanyDB\Service;
use yii\helpers\ArrayHelper;

class TestController extends Controller
{
	public function options($id){
		return ['sss'];
	}
	public $sss='m';
	public function actionIndex()
	{

		//$data=ArrayHelper::map(Service::find()->orderBy('name')->all(),'id','name');
		$data=")))";
		//print();
		$file=Yii::getAlias('@temp/shopTest.txt');
		file_put_contents($file,print_r($data,true));
	}
}
