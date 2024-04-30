<?php

use yii\bootstrap5\Html;
use yii\bootstrap5\LinkPager;
use yii\grid\GridView;
use yii\widgets\Pjax;


/** @var yii\web\View $this */
/** @var app\models\CollectionUploadedSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
$this->title = '文件收集上传日志';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="access-logs">
    <h1><?= Html::encode($this->title) ?></h1>
    <br>
    <?php Pjax::begin(); ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            [
                'attribute' => 'user_id',
                'label' => '用户',
                'format' => 'html',
                'value' => function ($model) {
                    return nl2br($model->user_id ? $model->user->username . "\n(ID:" . $model->user_id . ')' : '访客');
                },
            ],
            [
                'attribute' => 'uploaded_at',
                'label' => '上传时间',
                'format' => 'html',
                'enableSorting' => false,
                'value' => function ($model) {
                    return nl2br($model->uploaded_at . "\n(" . Yii::$app->formatter->asRelativeTime(new DateTime($model->uploaded_at, new DateTimeZone('GMT+8'))) . ")");
                },
            ],
            [
                'attribute' => 'task_id',
            ],
            [
                'attribute' => 'uploader_ip',
                'label' => 'IP地址',
                'format' => 'raw',
                'value' => function ($model) {
                    return Html::a($model->uploader_ip, 'https://ipinfo.io/' . $model->uploader_ip, ['target' => '_blank']);
                },
            ],

            [
                'attribute' => 'user_agent',
                'label' => 'User Agent',
            ],
            'note'
        ],
        'pager' => [
            'class' => LinkPager::class,
        ],
    ]);

    ?>

    <?php Pjax::end(); ?>

</div>