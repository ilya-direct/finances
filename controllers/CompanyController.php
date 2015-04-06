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
	    $sql='insert into company (name,link) values("reStore","reStore.com")';
	    $command=$connection->createCommand($sql);
	    //$dataReader=$command->queryAll();
	    $dataReader=$command->execute();
	    //$dataReader=$command->queryColumn();
        return $this->render('index',['companies'=>$dataReader]);
    }
}