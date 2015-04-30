<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use wbraganca\dynamicform\DynamicFormWidget;

/* @var $this yii\web\View */
/* @var $company app\models\company */
/* @var $companyAssign app\models\device */
/* @var $form yii\widgets\ActiveForm */

?>


<div class="company-form">
	<?php $form = ActiveForm::begin(['id' => 'dynamic-form']); ?>
	<div class="row">
		<div class="col-sm-6">
			<?= $form->field($company, 'name')->textInput(['maxlength' => 65]) ?>
		</div>
		<div class="col-sm-6">
			<?= $form->field($company, 'link')->textInput(['maxlength' => 45]) ?>
		</div>
	</div>

	<div class="panel panel-default">
		<div class="panel-body">
			<?php DynamicFormWidget::begin([
				'widgetContainer' => 'dynamicform_wrapper', // required: only alphanumeric characters plus "_" [A-Za-z0-9_]
				'widgetBody' => '.container-items', // required: css class selector
				'widgetItem' => '.item', // required: css class
				'limit' => 10, // the maximum times, an element can be cloned (default 999)
				'min' => 1, // 0 or 1 (default 1)
				'insertButton' => '.add-item', // css class
				'deleteButton' => '.remove-item', // css class
				'model' => $companyAssign[0],
				'formId' => 'dynamic-form',
				'formFields' => [
					'price',
					'device_id',
					'service_id'
				],
			]); ?>

			<div class="container-items"><!-- widgetContainer -->
				<?php foreach ($companyAssign as $i => $ca): ?>
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
							if (! $ca->isNewRecord) {
								echo Html::activeHiddenInput($ca, "[{$i}]id");
							}
							?>
							<div class="row">
								<div class="col-sm-8">
									<?= $form->field($ca, "[{$i}]service_id")->dropDownList($services,['prompt'=>'Выберете услугу']) ?>
								</div>
								<div class="col-sm-6">
									<?= $form->field($ca, "[{$i}]device_id")->dropDownList($devices,['prompt'=>'Выберете устройство']) ?>
								</div>
								<div class="col-sm-6">
									<?= $form->field($ca, "[{$i}]price")->textInput(['maxlength' => true]) ?>
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
		<?= Html::submitButton($company->isNewRecord ? 'Create' : 'Update', ['class' => $company->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
	</div>

	<?php ActiveForm::end(); ?>

</div>
