<?php

use yii\bootstrap5\Html;
use yii\bootstrap5\LinkPager;
use yii\grid\GridView;
use yii\widgets\Pjax;


/** @var yii\web\View $this */
/** @var app\models\DownloadLogs $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
$this->title = '分享访问日志';
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
                'attribute' => 'access_time',
                'label' => '访问时间',
                'format' => 'html',
                'enableSorting' => false,
                'value' => function ($model) {
                    return nl2br($model->access_time . "\n(" . Yii::$app->formatter->asRelativeTime(new DateTime($model->access_time, new DateTimeZone('GMT+8'))) . ")");
                },
            ],
            [
                    'label' => '目标文件/文件夹',
                    'format' => 'html',
                'value' => function($model){
                    return '用户ID'.$model->share->sharer_id . '/' . $model->share->file_relative_path;
                }
            ],
            [
                'attribute' => 'ip_address',
                'label' => 'IP地址',
                'format' => 'raw',
                'value' => function ($model) {
                    return Html::a($model->ip_address, 'https://ipinfo.io/'.$model->ip_address, ['target' => '_blank']);
                },
            ],

            [
                'attribute' => 'user_agent',
                'label' => 'User Agent',
            ],
        ],
        'pager' => [
            'class' => LinkPager::class,
        ],
    ]);

    ?>

    <?php Pjax::end(); ?>

</div>