<?php

use app\models\Announcements;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var app\models\AnnouncementsSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = '公告管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="announcements-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('发布公告', ['announcements-create'], ['class' => 'btn btn-success']) ?>
    </p>
    <p>Tips: 只有最新的三条公告对用户可见</p>
    <?php Pjax::begin(); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            'id',
            [
                'attribute' => 'title',
                'format' => 'raw',
                'value' => function (Announcements $model) {
                    return Html::a($model->title, ['announcements-view', 'id' => $model->id]);
                }
            ],
            [
                'attribute' => 'content',
                'label' => '内容',
                // if the content is too long, only show the first 30 characters
                'value' => function (Announcements $model) {
                    return mb_substr($model->content, 0, 30) . '...';
                }

            ],
            'published_at',
            'updated_at',
            [
                'class' => ActionColumn::class,
                'urlCreator' => function ($action, Announcements $model, $key, $index, $column) {
                    if ($action === 'view') {
                        return Url::to(['announcements-view', 'id' => $model->id]);
                    } elseif ($action === 'update') {
                        return Url::to(['announcements-update', 'id' => $model->id]);
                    } else { //delete
                        return Url::to(['announcements-delete', 'id' => $model->id]);
                    }
                }
            ],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>
