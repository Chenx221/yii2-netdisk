<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\User $model */
/** @var ActiveForm $form */
?>
<div class="user-register">

    <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($model, 'username')->label('用户名') ?>
        <?= $form->field($model, 'password')->label('密码') ?>
        <?= $form->field($model, 'email')->label('电子邮箱') ?>
    
        <div class="form-group">
            <?= Html::submitButton('注册', ['class' => 'btn btn-primary']) ?>
        </div>
    <?php ActiveForm::end(); ?>

</div><!-- user-register -->
