<?php

use yii\helpers\Html;
use yii\web\YiiAsset;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\Announcements $model */

$this->title = '公告ID '.$model->id;
$this->params['breadcrumbs'][] = ['label' => '公告管理', 'url' => ['announcements-manage']];
$this->params['breadcrumbs'][] = $this->title;
YiiAsset::register($this);
?>
<div class="announcements-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('修改公告', ['announcements-update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('删除公告', ['announcements-delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => '你确定要删除这条公告?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'title',
            'content:ntext',
            'published_at',
            'updated_at',
        ],
    ]) ?>

</div>
