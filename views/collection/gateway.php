<?php

use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\models\CollectionTasks $model */

$this->title = '请输入访问凭证';
$this->params['breadcrumbs'][] = '文件收集';
?>
<div class="collection-gateway">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php $form = ActiveForm::begin([
        'action' => Url::to(['collection/access', 'id' => Yii::$app->request->get('id')]),
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'secret')->textInput(['maxlength' => true])->label('访问凭证:') ?>

    <div class="form-group">
        <?= Html::submitButton('提交', ['class' => 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>