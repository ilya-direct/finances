<?php

namespace app\controllers;
use yii\web\Controller;
use app\models\company;
use app\models\service;
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
	    $query=(new \yii\db\Query())
		        //->select('c.name,c.link,c.backtrace')
		       // ->select(['company_name'=>'c.name'])
		        ->select(array_merge(Company::getColumns(),Service::getColumns()))
		        ->from('company c')
		        ->leftjoin('company_assign ca', 'ca.company_id=c.id')
		        ->leftjoin('device d', 'ca.device_id=d.id')
		        ->leftjoin('device s', 'ca.service_id=s.id');
	    $companies=$query->all();
/*
	    $subQuery = (new \yii\db\Query())->select('name')->from('company')->where('id=ca.company_id');

	    $query=(new \yii\db\Query())
		    //->select('c.name,c.link,c.backtrace')
		    ->select(['ca.id','CompName'=>$subQuery])
		    ->from('company_assign ca');
	    $companies=$query->all();
*/
	    //$dataReader=$command->queryAll();
	    //$dataReader=$command->execute();
	    //$dataReader=$command->queryColumn();

	    $company=Company::findOne(34);
	    $company->name='HelloApple2';
	    $company->link='HelloApple2.com';
	    $company->advantage='';
	    $company->disadvantage='';
	    $company->comment='wewewe';
	    if(!$company->save()){
		    var_dump($company->getErrors('comment'));
	    }
	    return;

        return $this->render('index',['companies'=>$companies]);
    }
	public function actionRelation(){
		$comp=company::findOne(1);
		$ca=$comp->companyAssign;
		//var_dump($ca);

		$comp=company::findOne(2);
		$ca=$comp->getCompanyAssign()->where(['>=','price',400])->all();
		$ca[0]->price=401;


		$comps=company::find()->joinWith(['companyAssign']);
		var_dump($comps);
//		$ca=$comps->companyAssign;
		var_dump($ca);
	}
}