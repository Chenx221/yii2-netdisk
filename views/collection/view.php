<?php

use yii\grid\GridView;
use yii\helpers\Html;
use yii\web\YiiAsset;
use yii\widgets\DetailView;
use app\models\CollectionUploadedSearch;

/** @var yii\web\View $this */
/** @var app\models\CollectionTasks $model */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Collection Tasks', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
YiiAsset::register($this);

$searchModel = new CollectionUploadedSearch();
$dataProvider = $searchModel->search(Yii::$app->request->queryParams);
$dataProvider->query->andWhere(['task_id' => $model->id]);
?>
<div class="collection-tasks-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'user_id',
            'folder_path',
            'created_at',
            'secret',
        ],
    ]) ?>

    <h2>收集情况:</h2>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            'id',
//            'task_id',
            'uploader_ip',
            'uploaded_at',
            'subfolder_name',
        ],
    ]); ?>
</div>
