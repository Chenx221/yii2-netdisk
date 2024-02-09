<?php

use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\User $model */
/** @var ActiveForm $form */

$this->title = '用户注册';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-register">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>请在下方输入注册用户所需的信息:</p>

    <div class="row">
        <div class="col-lg-5">
            <?php $form = ActiveForm::begin(); ?>

            <?= $form->field($model, 'username')->label('用户名')->textInput(['autofocus' => true]) ?>
            <?= $form->field($model, 'password')->passwordInput()->label('密码') ?>
            <?= $form->field($model, 'password2')->passwordInput()->label('重复密码') ?>
            <?= $form->field($model, 'email')->label('电子邮箱') ?>

            <div class="form-group">
                <?= Html::submitButton('注册', ['class' => 'btn btn-primary']) ?>
            </div>
            <div class="form-group">
                <?= Html::a('已经有账户? 点击登录', ['user/login']) ?>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div><!-- user-register -->
