<?php
/** @var app\models\EntryForm $model */

use yii\helpers\Html;

?>
<p>你提交了以下信息</p>
<ul>
    <li><label>Name:</label><?=
        Html::encode($model->name) ?></li>
    <li><label>Email:</label><?= Html::encode($model->email) ?></li>
</ul>
