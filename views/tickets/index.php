<?php

use app\assets\FontAwesomeAsset;
use app\models\Tickets;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var app\models\TicketsSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = '工单支持';
$this->params['breadcrumbs'][] = $this->title;
FontAwesomeAsset::register($this);
?>
<div class="tickets-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('创建工单', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php Pjax::begin(); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            [
                'attribute' => 'id',
                'label' => '工单ID',
            ],
            [
                'attribute' => 'title',
                'label' => '标题',
                'format' => 'raw', // 使用 raw 格式，这样 Yii2 不会对 value 的返回值进行 HTML 编码
                'value' => function (Tickets $model) {
                    return Html::a($model->title, ['view', 'id' => $model->id]);
                },
            ],
            [
                'attribute' => 'created_at',
                'label' => '创建时间',
                'filter' => false
            ],
            [
                'attribute' => 'updated_at',
                'label' => '最近更新时间',
                'value' => function (Tickets $model) {
                    return Yii::$app->formatter->asRelativeTime(new DateTime($model->updated_at, new DateTimeZone('GMT+8')));
                },
                'filter' => false
            ],
            [
                'attribute' => 'status',
                'label' => '状态',
                'format' => 'raw', // 使用 raw 格式，这样 Yii2 不会对 value 的返回值进行 HTML 编码
                'value' => function (Tickets $model) {
                    return match ($model->status) {
                        Tickets::STATUS_OPEN => '<span class="badge rounded-pill bg-primary">工单已开启</span>',
                        Tickets::STATUS_ADMIN_REPLY => '<span class="badge rounded-pill bg-info">管理员已回复</span>',
                        Tickets::STATUS_USER_REPLY => '<span class="badge rounded-pill bg-secondary">用户已回复</span>',
                        Tickets::STATUS_CLOSED => '<span class="badge rounded-pill bg-success">工单已关闭</span>',
                        default => '<span class="badge rounded-pill bg-danger">未知状态</span>',
                    };
                },
                'filter' => [
                    Tickets::STATUS_OPEN => '工单已开启',
                    Tickets::STATUS_ADMIN_REPLY => '管理员已回复',
                    Tickets::STATUS_USER_REPLY => '用户已回复',
                    Tickets::STATUS_CLOSED => '工单已关闭',
                ]
            ],
            [
                'class' => ActionColumn::class,
                'template' => '{view} {delete}',
                'buttons' => [
                    'view' => function ($url, $model, $key) {
                        return Html::a('<i class="fa-solid fa-eye"></i>', $url, [
                            'title' => '查看工单',
                            'data-pjax' => '0',
                        ]);
                    },
                    'delete' => function ($url, $model, $key) {
                        if ($model->status !== Tickets::STATUS_CLOSED) {
                            return Html::a('<i class="fa-solid fa-xmark"></i>', ['delete', 'id' => $model->id, 'from' => 'index'], [
                                'title' => '关闭工单',
                                'data-pjax' => '0',
                                'data-confirm' => '你确定要关闭这个工单吗？',
                                'data-method' => 'post',
                            ]);
                        } else {
                            return Html::tag('i', '', [
                                'class' => 'fa-solid fa-xmark',
                                'style' => 'color: gray;',
                                'title' => '工单已关闭',
                            ]);
                        }
                    },
                ],
                'urlCreator' => function ($action, Tickets $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                },
            ],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>
