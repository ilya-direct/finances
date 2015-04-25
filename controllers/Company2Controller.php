<?php

namespace app\controllers;

use app\models\dynamic_form;
use app\models\Service;
use Yii;
use app\models\company;
use app\models\companyAssign;
use app\models\device;
use app\models\Model;
use app\models\Company2Search;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;


/**
 * Company2Controller implements the CRUD actions for company model.
 */
class Company2Controller extends Controller
{
    public function behaviors()
    {
        return [
/*	        'access' =>[
		        'class' => AccessControl::classname(),
		        'only'=>['create','update'],
		        'rules'=>
			        [
				         'allow' => true,
				        'action'=>['index'],
                        'roles' => ['demo'],
			        ],
		        ],*/
	        'access' => [
		        'class' => AccessControl::className(),
		        //'only' => ['index','update','create'],
		        'rules' => [
			        [
				        'allow' => true,
				        'actions' => ['index','create','update','view'],
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
        $searchModel = new Company2Search();
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

    /**
     * Creates a new company model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */

  /*  public function actionCreate()
    {
        $model = new company();
        $modelDevices =companyAssign::find()->all();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
	            'modelDevices'=>$modelDevices
            ]);
        }
    }*/

    /**
     * Updates an existing company model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
/*    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }*/

    /**
     * Deletes an existing company model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
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
		$services=ArrayHelper::map(Service::find()->all(),'id','name');
		ksort($services);
		$devices=ArrayHelper::map(Device::find()->all(),'id','name');
		ksort($devices);

		if ($company->load(Yii::$app->request->post())) {

			$companyAssign = dynamic_form::createMultiple(CompanyAssign::classname());
			dynamic_form::loadMultiple($companyAssign, Yii::$app->request->post());

			// ajax validation
			/*
			if (Yii::$app->request->isAjax) {
				Yii::$app->response->format = Response::FORMAT_JSON;
				return ArrayHelper::merge(
					ActiveForm::validateMultiple($companyAssign),
					ActiveForm::validate($companyAssign)
				);
			}
			*/

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
			'model' => $company,
			'modelDevices'=> (empty($companyAssign)) ? [new $companyAssign] : $companyAssign,
			'services'=>$services,
			'devices'=>$devices,
//			'modelCompany' => $company,
//			'modelsCompanyAssign' => (empty($companyAssign)) ? [new $companyAssign] : $companyAssign
		]);
	}

	/**
	 * Updates an existing Customer model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id
	 * @return mixed
	 */
	public function actionUpdate($id)
	{
		$company = $this->findModel($id);
		$companyAssign = $company->companyAssign;
		$services=ArrayHelper::map(Service::find()->all(),'id','name');
		ksort($services);
		$devices=ArrayHelper::map(Device::find()->all(),'id','name');
		ksort($devices);

		if ($company ->load(Yii::$app->request->post())) {

			$oldIDs = ArrayHelper::map($companyAssign, 'id', 'id');
			$companyAssign = dynamic_form::createMultiple(CompanyAssign::classname(), $companyAssign );
			dynamic_form::loadMultiple( $companyAssign, Yii::$app->request->post());
			$deletedIDs = array_diff($oldIDs, array_filter(ArrayHelper::map($companyAssign, 'id', 'id')));
			// ajax validation
			// validate all models


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
}
