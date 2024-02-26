<?php

use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\models\CollectionTasks $model */
/** @var app\models\CollectionUploaded $model2 */

$this->title = '文件收集';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="collection-access">

    <h1><?= Html::encode($this->title) ?></h1>
    <p>
        这是文件收集任务<?= Html::encode($model->id) ?>，上传的文件将会保存到预先设定的位置。
    </p>

    <p>
        上传者UUID: <?= Html::encode($model2->subfolder_name) ?>
    </p>

    <?php $form = ActiveForm::begin([
        'action' => Url::to(['collection/upload']),
        'method' => 'post',
        'options' => ['enctype' => 'multipart/form-data']
    ]); ?>

    <label for="uploader">要上传的文件:</label>
    <input type="file" multiple name="files[]" id="uploader">

    <?= $form->field($model, 'id')->hiddenInput()->label(false) ?>

    <?= $form->field($model2, 'subfolder_name')->hiddenInput()->label(false) ?>

    <div class="form-group">
        <?= Html::submitButton('上传文件', ['class' => 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
