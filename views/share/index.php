<?php

use app\models\Share;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var app\models\ShareSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = '分享';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="share-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('创建分享', ['home/index'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'share_id',
//            'sharer_id',
            'file_relative_path',
            'access_code',
            'creation_date',
            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, Share $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'share_id' => $model->share_id]);
                 }
            ],
        ],
    ]); ?>


</div>
