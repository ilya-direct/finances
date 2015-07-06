<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\company */

$this->title = 'Добавление новой компании';
$this->params['breadcrumbs'][] = ['label' => 'Список компаний', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="company-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'company' => $company,
	    'companyAssign'=>$companyAssign,
	    'devices'=>$devices,
        'services'=>$services,
    ]) ?>

</div>
