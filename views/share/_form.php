<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Share $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="share-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= Html::label($model->getAttributeLabel('sharer_id'), 'sharer_id', ['class' => 'control-label']) ?>
    <?= Html::tag('p', Html::encode($model->sharer_id), ['class' => 'form-control-static']) ?>

    <?= Html::label($model->getAttributeLabel('file_relative_path'), 'file_relative_path', ['class' => 'control-label']) ?>
    <?= Html::tag('p', Html::encode($model->file_relative_path), ['class' => 'form-control-static']) ?>

    <?= $form->field($model, 'access_code')->textInput(['maxlength' => 4]) ?>

    <?= Html::label($model->getAttributeLabel('creation_date'), 'creation_date', ['class' => 'control-label']) ?>
    <?= Html::tag('p', Html::encode($model->creation_date), ['class' => 'form-control-static']) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>