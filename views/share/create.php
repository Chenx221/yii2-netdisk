<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Share $model */

$this->title = 'Create Share';
$this->params['breadcrumbs'][] = ['label' => 'Shares', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="share-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
