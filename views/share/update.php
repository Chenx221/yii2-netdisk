<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Share $model */

$this->title = '更新分享ID' . $model->share_id . '的访问密码';
$this->params['breadcrumbs'][] = ['label' => 'Shares', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->share_id, 'url' => ['view', 'share_id' => $model->share_id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="share-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
