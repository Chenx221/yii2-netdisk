<?php

use yii\grid\GridView;
use yii\helpers\Html;
use yii\web\JqueryAsset;
use yii\web\View;
use yii\web\YiiAsset;
use yii\widgets\DetailView;
use app\models\CollectionUploadedSearch;

/** @var yii\web\View $this */
/** @var app\models\CollectionTasks $model */

$this->title = '文件收集ID ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => '文件收集', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
YiiAsset::register($this);

$searchModel = new CollectionUploadedSearch();
$dataProvider = $searchModel->search(Yii::$app->request->queryParams);
$dataProvider->query->andWhere(['task_id' => $model->id]);
?>
<div class="collection-tasks-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('复制收集链接', null, ['class' => 'btn btn-primary', 'id' => 'copy-link-button']) ?>
        <?= Html::a('访问收集链接', ['collection/access', 'id' => $model->id, '$secret' => $model->secret], ['class' => 'btn btn-primary', 'target' => '_blank']) ?>
        <?= Html::a('取消收集', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => '你确定要取消这个收集任务吗？已收集的文件不会被删除',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'folder_path',
            'created_at',
            'secret',
        ],
    ]) ?>

    <h2>文件收集情况:</h2>
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
<?php
$this->registerJsFile('@web/js/collection_view.js', ['depends' => [JqueryAsset::class], 'position' => View::POS_END]);
?>
