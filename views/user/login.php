<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\User $model */
/** @var ActiveForm $form */
?>
<div class="user-login">

    <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($model, 'username')->label('用户名') ?>
        <?= $form->field($model, 'password')->label('密码') ?>

        <div class="form-group">
            <?= Html::submitButton('登录', ['class' => 'btn btn-primary']) ?>
        </div>
    <?php ActiveForm::end(); ?>

</div><!-- user-login -->
