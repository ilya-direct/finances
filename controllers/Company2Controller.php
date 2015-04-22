<?php

namespace app\controllers;

use Yii;
use app\models\company;
use app\models\device;
use app\models\Company2Search;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

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
    public function actionCreate()
    {
        $model = new company();
        $modelDevices =Device::find()->all();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
	            'modelDevices'=>$modelDevices
            ]);
        }
    }

    /**
     * Updates an existing company model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

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
}
