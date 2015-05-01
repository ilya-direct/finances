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

			$companyAssign = dynamic_form::createMultiple(CompanyAssign::classname());
			dynamic_form::loadMultiple($companyAssign, Yii::$app->request->post());

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
			$companyAssign = dynamic_form::createMultiple(CompanyAssign::classname(), $companyAssign );
			dynamic_form::loadMultiple( $companyAssign, Yii::$app->request->post());
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
}
