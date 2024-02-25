<?php

use yii\helpers\Html;
use yii\web\JqueryAsset;
use yii\web\View;
use yii\web\YiiAsset;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\Share $model */

$this->title = '分享ID '.$model->share_id;
$this->params['breadcrumbs'][] = ['label' => '分享', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
YiiAsset::register($this);
JqueryAsset::register($this);
?>
<div class="share-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('复制分享链接', null, ['class' => 'btn btn-primary', 'id' => 'copy-link-button']) ?>
        <?= Html::a('访问分享链接', ['share/access', 'share_id' => $model->share_id,'access_code'=>$model->access_code], ['class' => 'btn btn-primary', 'target' => '_blank']) ?>
        <?= Html::a('修改分享', ['update', 'share_id' => $model->share_id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('取消分享', ['delete', 'share_id' => $model->share_id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => '你确定要取消这个分享？其他人将无法通过该链接访问文件',
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
<?php
$this->registerJsFile('@web/js/share_view.js', ['depends' => [JqueryAsset::class], 'position' => View::POS_END]);
?>
