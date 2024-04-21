<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Announcements $model */

$this->title = '修改公告: ' . $model->title;
$this->params['breadcrumbs'][] = ['label' => '公告管理', 'url' => ['announcements-manage']];
$this->params['breadcrumbs'][] = ['label' => '公告ID '.$model->id, 'url' => ['announcements-view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = '修改公告';
?>
<div class="announcements-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_announcements_form', [
        'model' => $model,
    ]) ?>

</div>
