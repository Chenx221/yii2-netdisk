<?php

/** @var yii\web\View $this */
/** @var app\models\Share $model */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

$this->title = '访问分享';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="share-access">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'access_code')->passwordInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton('提交', ['class' => 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>