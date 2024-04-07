<?php

use yii\helpers\Html;
use yii\web\JqueryAsset;
use yii\web\View;
use yii\web\YiiAsset;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\Share $model */

$this->title = '分享ID ' . $model->share_id;
$this->params['breadcrumbs'][] = ['label' => '文件分享管理', 'url' => ['share-manage']];
$this->params['breadcrumbs'][] = $this->title;
YiiAsset::register($this);
JqueryAsset::register($this);
?>
<div class="share-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?php if ($model->status != 0): ?>
            <?= Html::a('复制分享链接', null, ['class' => 'btn btn-primary', 'id' => 'copy-link-button']) ?>
            <?= Html::a('访问分享链接', ['share/access', 'share_id' => $model->share_id, 'access_code' => $model->access_code], ['class' => 'btn btn-primary', 'target' => '_blank']) ?>
            <?= Html::a('取消分享', ['share-manage-delete', 'share_id' => $model->share_id], [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => '确定要取消分享？',
                    'method' => 'post',
                ],
            ]) ?>
        <?php else: ?>
            <?= Html::a('复制分享链接', null, ['class' => 'btn btn-primary disabled', 'id' => 'copy-link-button', 'aria-disabled' => 'true']) ?>
            <?= Html::a('访问分享链接', null, ['class' => 'btn btn-primary disabled', 'target' => '_blank', 'aria-disabled' => 'true']) ?>
            <?= Html::a('取消分享', null, [
                'class' => 'btn btn-danger disabled',
                'aria-disabled' => 'true'
            ]) ?>
        <?php endif; ?>


    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'share_id',
            'sharer_id',
            'file_relative_path',
            'access_code',
            'creation_date',
            [
                'attribute' => 'status',
                'label' => '分享状态',
                'value' => function ($model) {
                    return $model->status == 1 ? '有效' : '失效';
                },
            ],
            [
                'attribute' => 'dl_count',
            ],
        ],
    ]) ?>

</div>
<?php
$this->registerJsFile('@web/js/share_view.js', ['depends' => [JqueryAsset::class], 'position' => View::POS_END]);
?>
