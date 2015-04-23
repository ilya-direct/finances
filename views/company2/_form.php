<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use wbraganca\dynamicform\DynamicFormWidget;

/* @var $this yii\web\View */
/* @var $model app\models\company */
/* @var $modelDevices app\models\device */
/* @var $form yii\widgets\ActiveForm */

?>


<div class="company-form">
	<?php $form = ActiveForm::begin(['id' => 'dynamic-form']); ?>
	<div class="row">
		<div class="col-sm-6">
			<?= $form->field($model, 'name')->textInput(['maxlength' => 65]) ?>
		</div>
		<div class="col-sm-6">
			<?= $form->field($model, 'link')->textInput(['maxlength' => 45]) ?>
		</div>
	</div>

	<div class="panel panel-default">
		<div class="panel-heading"><h4><i class="glyphicon glyphicon-envelope"></i> Услуги</h4></div>
		<div class="panel-body">
			<?php DynamicFormWidget::begin([
				'widgetContainer' => 'dynamicform_wrapper', // required: only alphanumeric characters plus "_" [A-Za-z0-9_]
				'widgetBody' => '.container-items', // required: css class selector
				'widgetItem' => '.item', // required: css class
				'limit' => 10, // the maximum times, an element can be cloned (default 999)
				'min' => 1, // 0 or 1 (default 1)
				'insertButton' => '.add-item', // css class
				'deleteButton' => '.remove-item', // css class
				'model' => $modelDevices[0],
				'formId' => 'dynamic-form',
				'formFields' => [
					'price',
					'device_id',
					'service_id'
				],
			]); ?>

			<div class="container-items"><!-- widgetContainer -->
				<?php foreach ($modelDevices as $i => $modelDevice): ?>
					<div class="item panel panel-default"><!-- widgetBody -->
						<div class="panel-heading">
							<h3 class="panel-title pull-left">Услуга</h3>
							<div class="pull-right">
								<button type="button" class="add-item btn btn-success btn-xs"><i class="glyphicon glyphicon-plus"></i></button>
								<button type="button" class="remove-item btn btn-danger btn-xs"><i class="glyphicon glyphicon-minus"></i></button>
							</div>
							<div class="clearfix"></div>
						</div>
						<div class="panel-body">
							<?php
							// necessary for update action.
							if (! $modelDevice->isNewRecord) {
								echo Html::activeHiddenInput($modelDevice, "[{$i}]id");
							}
							?>
							<div class="row">
								<div class="col-sm-6">
									<?= $form->field($modelDevice, "[{$i}]service_id")->dropDownList($services) ?>
								</div>
								<div class="col-sm-7">
									<?= $form->field($modelDevice, "[{$i}]device_id")->dropDownList($devices) ?>
								</div>
								<div class="col-sm-6">
									<?= $form->field($modelDevice, "[{$i}]price")->textInput(['maxlength' => true]) ?>
								</div>
							</div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
			<?php DynamicFormWidget::end(); ?>
		</div>
	</div>

	<div class="form-group">
		<?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
	</div>

	<?php ActiveForm::end(); ?>

</div>
