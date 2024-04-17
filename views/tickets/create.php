<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Tickets $model */

$this->title = '创建工单';
$this->params['breadcrumbs'][] = ['label' => '工单支持', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="tickets-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>


