<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\CollectionTasks $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="collection-tasks-form">

    <?php $form = ActiveForm::begin([
        'action' => ['collection/create'], // 指定表单提交的URL
    ]); ?>

    <?= $form->field($model, 'folder_path')->textInput(['maxlength' => true, 'readonly' => true]) ?>

    <?= $form->field($model, 'secret')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton('创建', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
