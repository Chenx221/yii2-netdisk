<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Announcements $model */

$this->title = '发布公告';
$this->params['breadcrumbs'][] = ['label' => '公告管理', 'url' => ['announcements-manage']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="announcements-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_announcements_form', [
        'model' => $model,
    ]) ?>

</div>
