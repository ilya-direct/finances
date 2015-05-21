<?php
namespace app\commands;

use yii\console\Controller;
use app\models\Service;
use yii\helpers\ArrayHelper;

class ShopController extends Controller
{
	public function actionIndex()
	{
		print_r(ArrayHelper::map(Service::find()->orderBy('name')->all(),'id','name'));
	}
}
