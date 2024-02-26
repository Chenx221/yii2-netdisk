<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\CollectionTasks $model */
/** @var app\models\CollectionUploaded $model2 */

$this->title = '文件收集';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="collection-access">

    <h1><?= Html::encode($this->title) ?></h1>
    <p>
        收集任务ID: <?= Html::encode($model->id) ?>
    </p>

    <p>
        访问密钥: <?= Html::encode($model->secret) ?>
    </p>

    <p>
        收集目标文件夹: <?= Html::encode($model2->subfolder_name) ?>
    </p>
</div>
