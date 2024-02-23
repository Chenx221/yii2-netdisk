<?php

use app\models\CollectionUploaded;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var app\models\CollectionUploadedSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = '收集上传记录';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="collection-uploaded-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'task_id',
            'uploader_ip',
            'uploaded_at',
            'subfolder_name',
            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, CollectionUploaded $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                 }
            ],
        ],
    ]); ?>


</div>
