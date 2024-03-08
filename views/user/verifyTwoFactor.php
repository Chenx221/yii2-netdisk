<?php

use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\User $model */

$this->title = '2FA验证';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-login-2fa">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>请在下方输入二步验证代码:</p>

    <div class="row">
        <div class="col-lg-5">
            <?php $form = ActiveForm::begin(); ?>

            <?= $form->field($model, 'totp_input')->passwordInput()->label('二步验证代码') ?>
            <div class="form-group">
                <?= Html::submitButton('提交', ['class' => 'btn btn-primary']) ?>
            </div>
            <?php ActiveForm::end(); ?>
            <hr>
            <?php $form = ActiveForm::begin(); ?>
            <?= $form->field($model, 'recoveryCode_input')->textInput()->label('丢失所有验证设备? 使用恢复代码') ?>
            <div class="form-group">
                <?= Html::submitButton('恢复账户', ['class' => 'btn btn-primary']) ?>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div><!-- user-login-2fa -->
