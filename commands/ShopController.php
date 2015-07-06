<?php
namespace app\commands;

use yii;
use yii\console\Controller;
use app\models\CompanyDB\Service;
use yii\helpers\ArrayHelper;

class ShopController extends Controller
{
	public function actionIndex()
	{

		$data=ArrayHelper::map(Service::find()->orderBy('name')->all(),'id','name');
//		print_r($data);
		//print();
		file_put_contents(dirname(__FILE__).'/shopTest.txt',print_r($data,true));
	}
}
