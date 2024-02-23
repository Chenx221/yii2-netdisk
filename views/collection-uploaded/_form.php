<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\CollectionUploaded $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="collection-uploaded-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'task_id')->textInput() ?>

    <?= $form->field($model, 'uploader_ip')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'uploaded_at')->textInput() ?>

    <?= $form->field($model, 'subfolder_name')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
