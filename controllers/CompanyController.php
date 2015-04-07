<?php

namespace app\controllers;
use yii\web\Controller;
use app\models\company;
use yii\web\YiiAsset;
use Yii;

class CompanyController extends Controller{

    public function actionIndex(){

        //$companies= Company::find()->all();
	    $connection=Yii::$app->db;
	    $sql='select name,id,backtrace from company where id<5';
	   // $sql='insert into company (name,link) values("reStore","reStore.com")';

	    $command=$connection->createCommand();
	    $companies=(new \yii\db\Query())->select('name,link,backtrace')->from('company')->where('id>10')->all();
	    $companies=(new \yii\db\Query())
		        ->select('name,link,backtrace')
		        ->from('company c')
		        ->join('company_assign ca', 'ca.company_id=c.id')
		        ->join('service s', 'ca.service_id=s.id')
		        ->join('device d', 'ca.device_id=d.id')
		        ->where('c.id=1')->all();

	    //$dataReader=$command->queryAll();
	    //$dataReader=$command->execute();
	    //$dataReader=$command->queryColumn();
        return $this->render('index',['companies'=>$companies]);
    }
}