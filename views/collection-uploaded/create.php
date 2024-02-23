<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\CollectionUploaded $model */

$this->title = 'Create Collection Uploaded';
$this->params['breadcrumbs'][] = ['label' => 'Collection Uploadeds', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="collection-uploaded-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
