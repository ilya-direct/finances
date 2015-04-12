<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\company */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="company-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => 65]) ?>

    <?= $form->field($model, 'link')->textInput(['maxlength' => 45]) ?>

    <?= $form->field($model, 'advantage')->textInput(['maxlength' => 100]) ?>

    <?= $form->field($model, 'disadvantage')->textInput(['maxlength' => 100]) ?>

    <?= $form->field($model, 'timecreated')->textInput() ?>

    <?= $form->field($model, 'comment')->textInput(['maxlength' => 100]) ?>

    <?= $form->field($model, 'backtrace')->textInput(['maxlength' => 100]) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
