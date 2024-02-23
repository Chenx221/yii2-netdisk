<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\CollectionTasks $model */

$this->title = 'new 文件收集';
$this->params['breadcrumbs'][] = ['label' => 'Collection Tasks', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="collection-tasks-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
