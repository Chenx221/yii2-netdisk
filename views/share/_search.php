<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\ShareSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="share-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'share_id') ?>

<!--    --><?php //= $form->field($model, 'sharer_id') ?>

    <?= $form->field($model, 'file_relative_path') ?>

    <?= $form->field($model, 'access_code') ?>

    <?= $form->field($model, 'creation_date') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
