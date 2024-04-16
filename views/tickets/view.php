<?php

use yii\helpers\Html;
use yii\web\YiiAsset;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\Tickets $model */

$this->title = '工单: '.$model->title;
$this->params['breadcrumbs'][] = ['label' => '工单支持', 'url' => ['index']];
$this->params['breadcrumbs'][] = '工单ID '.$model->id;
YiiAsset::register($this);
?>
<div class="tickets-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('关闭工单', ['delete', 'id' => $model->id], [
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
            'title',
            'description:ntext',
            'status',
            'created_at',
            'updated_at',
            'ip',
        ],
    ]) ?>

</div>
