<?php
/** @var ActiveDataProvider $dataProvider */

use yii\bootstrap5\Html;
use yii\data\ActiveDataProvider;
use yii\grid\ActionColumn;
use yii\grid\GridView;
echo '<div id="pjax-container">';
echo GridView::widget([
    'dataProvider' => $dataProvider,
    'layout' => "{items}",
    'columns' => [
        [
            'attribute' => 'name',
            'label' => '设备名',
        ],
        [
            'attribute' => 'public_key_credential_id',
            'label' => '公钥凭证ID',
        ],
        [
            'class' => ActionColumn::class,
            'template' => '{delete}',
            'buttons' => [
                'delete' => function ($url, $model, $key) {
                    return Html::a('<i class="fa-solid fa-trash"></i>', ['credential-delete', 'id' => $model->id], [
                        'data' => [
                            'confirm' => '你确定要删除这个项目吗?',
                            'method' => 'post',
                            'pjax' => 1,
                        ],
                    ]);
                },
            ],
        ],
    ],
]);
echo '</div>';