<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\CollectionUploaded $model */

$this->title = 'Update Collection Uploaded: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Collection Uploadeds', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="collection-uploaded-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
