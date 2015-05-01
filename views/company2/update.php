<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\company */

$this->title = 'Изменение данных компании : ' . ' ' . $company->name;
$this->params['breadcrumbs'][] = ['label' => 'Список компаний', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $company->name, 'url' => ['view', 'id' => $company->id]];
$this->params['breadcrumbs'][] = 'Изменить';
?>
<div class="company-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'company' => $company,
	    'companyAssign'=>$companyAssign,
	   // 'service_group'=> $service_group,
	    'services'=>$services,
	    'devices'=>$devices
    ]) ?>

</div>
