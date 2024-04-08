<?php

use app\models\Share;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var app\models\ShareSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = '文件分享管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="share-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            [
                'attribute' => 'share_id',
                'headerOptions' => ['style' => 'width:6%;'],
            ],
            [
                'attribute' => 'sharer_id',
                'label' => '分享者',
                'value' => function ($model) {
                    return $model->getSharerUsername() . ' (ID:' . $model->sharer_id . ')';
                },
            ],
            [
                'attribute' => 'file_relative_path',
            ],
            [
                'attribute' => 'access_code',
                'enableSorting' => false,
                'headerOptions' => ['style' => 'width:7%;'],
            ],
            [
                'attribute' => 'creation_date',
                'headerOptions' => ['style' => 'width:15%;'],
            ],
            [
                'attribute' => 'status',
                'label' => '分享状态',
                'value' => function ($model) {
                    return $model->status == 1 ? '有效' : '失效';
                },
                'filter' => [1 => '有效', 0 => '失效'],
            ],
            [
                'attribute' => 'dl_count',
                'label' => '下载次数',
            ],
            [
                'class' => ActionColumn::class,
                'template' => '{view} {delete}', // 只显示查看和删除按钮
                'urlCreator' => function ($action, Share $model, $key, $index, $column) {
                    if ($action === 'view') {
                        // 返回查看按钮的链接
                        return Url::toRoute(['share-manage-view', 'share_id' => $model->share_id]);
                    } else {
                        // 如果 status 为 0，禁用删除按钮
                        return $model->status != 0 ? Url::toRoute(['share-manage-delete', 'share_id' => $model->share_id]) : null;
                    }
                }
            ],
        ],
    ]); ?>


</div>
