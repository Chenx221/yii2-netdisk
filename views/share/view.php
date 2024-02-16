<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\Share $model */

$this->title = $model->share_id;
$this->params['breadcrumbs'][] = ['label' => 'Shares', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="share-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'share_id' => $model->share_id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'share_id' => $model->share_id], [
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
            'share_id',
//            'sharer_id',
            'file_relative_path',
            'access_code',
            'creation_date',
        ],
    ]) ?>

</div>
