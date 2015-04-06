<?php

namespace app\controllers;
use yii\web\Controller;
use app\models\company;


class CompanyController extends Controller{
    public function actionIndex(){
        $companies= Company::find()->all();
        return $this->render('index',['companies'=>$companies]);
    }
}