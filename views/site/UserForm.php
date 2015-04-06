<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
?>

<?
    if(Yii::$app->session->hasFlash('success')){
        echo "<div class='alert alert-success'>". Yii::$app->session->getFlash('success')."</div>";
    }
?>

<? $form=ActiveForm::begin(); ?>
<?=$form->field($model,'name'); ?>
<?=$form->field($model,'email'); ?>

<?= Html::submitButton('Submit',['class'=>'btn btn-success']);
