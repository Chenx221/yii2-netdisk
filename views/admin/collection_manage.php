<?php

use app\models\CollectionTasks;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var app\models\CollectionSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = '文件收集管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="collection-tasks-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            [
                'attribute' => 'id',
                'headerOptions' => ['style' => 'width:6%;'],
            ],
            [
                'attribute' => 'user_id',
                'label' => '收集者',
                'value' => function ($model) {
                    return $model->user->username . ' (ID:' . $model->user_id . ')';
                },
            ],
            'folder_path',
            'created_at',
            'secret',
            [
                'attribute' => 'status',
                'label' => '收集状态',
                'value' => function ($model) {
                    return $model->status == 1 ? '有效' : '失效';
                },
                'filter' => [1 => '有效', 0 => '失效'],
            ],
            [
                'class' => ActionColumn::class,
                'template' => '{view} {delete}',
                'urlCreator' => function ($action, CollectionTasks $model, $key, $index, $column) {
                    if ($action === 'view') {
                        // 返回查看按钮的链接
                        return Url::toRoute(['collection-manage-view', 'id' => $model->id]);
                    } else {
                        // 如果 status 为 0，禁用删除按钮
                        return $model->status != 0 ? Url::toRoute(['collection-manage-delete', 'id' => $model->id]) : null;
                    }
                 }
            ],
        ],
    ]); ?>


</div>
