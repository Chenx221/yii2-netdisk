<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\User $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="user-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'username')->textInput(['maxlength' => true])->label('用户名') ?>

    <?= $form->field($model, 'email')->input('email')->label('电子邮箱地址') ?>

    <?= $form->field($model, 'password')->passwordInput(['maxlength' => true])->label('密码') ?>

    <div class="form-group field-user-role">
        <div id="user-role" class="form-check" role="radiogroup">
            <input class="form-check-input" type="radio" name="User[role]" value="user" id="userRadio" checked>
            <label class="form-check-label" for="userRadio">
                用户
            </label>
        </div>
        <div id="user-role" class="form-check" role="radiogroup">
            <input class="form-check-input" type="radio" name="User[role]" value="admin" id="adminRadio">
            <label class="form-check-label" for="adminRadio">
                管理员
            </label>
        </div>
    </div>

    <div class="form-group">
        <?= Html::submitButton('创建', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
