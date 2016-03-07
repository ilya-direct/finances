<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $name string */
/* @var $message string */
/* @var $exception Exception */

$this->title = $exception->statusCode;
?>
<div class="site-error">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="alert alert-danger">
        <?= nl2br(Html::encode($message)) ?>
    </div>

    <p>
        Мы уже знаем, о данной ошибке! Исправим в ближайшее время!
    </p>
	<p>
		По всем вопросам обращайтесь по телефону 8-963-656-83-77
	</p>

</div>
