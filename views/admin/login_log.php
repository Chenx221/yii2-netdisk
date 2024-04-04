<?php

use yii\bootstrap5\Html;
use yii\bootstrap5\LinkPager;
use yii\grid\GridView;
use yii\helpers\Url;
use yii\widgets\Pjax;


/** @var yii\web\View $this */
/** @var app\models\LoginLogs $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
$this->title = '登录日志';
?>
<div class="login-logs">
    <h1><?= Html::encode($this->title) ?></h1>
    <br>
    <?php Pjax::begin(); ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            [
                'attribute' => 'user_id',
                'label' => '试图登录的账户',
                'format' => 'html', // 设置格式为 HTML
                'value' => function ($model) {
                    return nl2br($model->user_id ? $model->user->username."\n(ID:".$model->user_id.')' : '不存在的用户');
                },
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
                'attribute' => 'login_time',
                'label' => '登录时间',
                'format' => 'html',
                'enableSorting' => false,
                'value' => function ($model) {
                    return nl2br($model->login_time . "\n(" . Yii::$app->formatter->asRelativeTime(new DateTime($model->login_time, new DateTimeZone('GMT+8'))) . ")");
                },
            ],
            [
                'attribute' => 'user_agent',
                'label' => 'User Agent',
            ],
            [
                'attribute' => 'status',
                'label' => '登录状态',
                'format' => 'html', // 设置格式为 HTML
                'value' => function ($model) {
                    return $model->status === 1 ? '<span class="badge bg-success">成功</span>' : '<span class="badge bg-danger">失败</span>';
                },
            ],
        ],
        'pager' => [
            'class' => LinkPager::class,
        ],
    ]);

    ?>

    <?php Pjax::end(); ?>

</div>