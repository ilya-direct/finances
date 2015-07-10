<?php

namespace app\controllers;

use app\models\CompanyModels\DynamicForm;
use app\models\CompanyDB\Service;
use Yii;
use app\models\CompanyDB\Company;
use app\models\CompanyDB\CompanyAssign;
use app\models\CompanyDB\Device;
use app\models\CompanyModels\CompanySearch;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;


/**
 * Company2Controller implements the CRUD actions for company model.
 */
class CompanyController extends Controller
{
    public function behaviors()
    {
        return [
	        'access' => [
		        'class' => AccessControl::className(),
		        //'only' => ['index','update','create'],
		        'rules' => [
			        [
				        'allow' => true,
				        'actions' => ['index','create','update','view','delete'],
				        'roles' => ['@'],
			        ],
			        [
				        'allow' => true,
				        'actions' => ['index','view'],
				        'roles' => ['?'],
			        ],
			    ],
		        ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Lists all company models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new CompanySearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single company model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the company model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return company the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = company::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }


	public function actionCreate()
	{
		$company = new Company;
		$companyAssign = [new companyAssign];
		$services=ArrayHelper::map(Service::find()->orderBy('name')->all(),'id','name');
		$devices= ArrayHelper::map(Device::find()->orderBy('name')->all(),'id','name');

		if ($company->load(Yii::$app->request->post())) {

			$companyAssign = DynamicForm::createMultiple(CompanyAssign::classname());
			DynamicForm::loadMultiple($companyAssign, Yii::$app->request->post());

			$transaction = \Yii::$app->db->beginTransaction();
			try {
				if ($flag = $company->save()) {
					foreach ($companyAssign as $ca) {
						$ca->company_id = $company->id;
						if (! ($flag = $ca->save())) {
							$transaction->rollBack();
							break;
						}
					}
				}
				if ($flag) {
					$transaction->commit();
					return $this->redirect(['view', 'id' => $company->id]);
				}
			} catch (\Exception $e) {
				$transaction->rollBack();
			}

		}

		return $this->render('create', [
			'company' => $company,
			'companyAssign'=> (empty($companyAssign)) ? [new $companyAssign] : $companyAssign,
			'services'=>$services,
			'devices'=>$devices,
		]);
	}

	public function actionUpdate($id)
	{
		$company = $this->findModel($id);
		$companyAssign = $company->companyAssign;
		$services=ArrayHelper::map(Service::find()->orderBy('name')->all(),'id','name');
		$devices=ArrayHelper::map(Device::find()->orderBy('name')->all(),'id','name');

		if ($company ->load(Yii::$app->request->post())) {

			$oldIDs = ArrayHelper::map($companyAssign, 'id', 'id');
			$companyAssign = DynamicForm::createMultiple(CompanyAssign::classname(), $companyAssign );
			DynamicForm::loadMultiple( $companyAssign, Yii::$app->request->post());
			$deletedIDs = array_diff($oldIDs, array_filter(ArrayHelper::map($companyAssign, 'id', 'id')));
			$transaction = \Yii::$app->db->beginTransaction();
			try {
				if ($flag = $company->save()) {
					if (! empty($deletedIDs)) {
						CompanyAssign::deleteAll(['id' => $deletedIDs]);
					}
					foreach ($companyAssign as $ca) {
						$ca->company_id = $company->id;
						if (! ($flag = $ca->save())) {
							$transaction->rollBack();
							break;
						}
					}
				}
				if ($flag) {
					$transaction->commit();
					return $this->redirect(['view', 'id' => $company->id]);
				}
			} catch (\Exception $e) {
				$transaction->rollBack();
			}
		}

		return $this->render('update', [
			'company' => $company,
			'companyAssign'=>$companyAssign,
			'services'=>$services,
			'devices'=>$devices
		]);
	}

	public function _actionIndex2(){

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
		 * //				DB\TransactionCategory::find()->where(['value'=>$headers[$i]])->exists()
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
		/*
		$company=Company::findOne(43);
		$company=is_object($company) ? $company : new company();
		$company->name='HelloApple2';
		$company->link='HelloApple2.com';
		$company->advantage='';
		$company->disadvantage='';
		$company->comment='macbook';
		if(!$company->save()){
			var_dump($company->getErrors('comment'));
		}
		*/

		$company=Company::findOne(1);
		$companyAssigns=$company->companyAssign;
		//  var_dump($companyAssigns);
		$service_group=array();
		foreach($companyAssigns as $ca){
			if(!isset($service_group[$ca->service_id])) $service_group[$ca->service_id]=array();
			$service_group[$ca->service_id][]=$ca;
		}
		var_dump($service_group);
		return;

		return $this->render('index',['companies'=>$companies]);
	}
	public function _actionRelation(){
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
