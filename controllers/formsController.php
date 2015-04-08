<?php

namespace app\controllers;

use yii\web\controller;
use app\models\company;

class UsersController extends Controller{

	public function actionIndex(){
		$companies = company::find()->all();
		return $this->render('index',['companies'=>$companies]);
		//var_dump($company);
	}
}