<?php

use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\User $model */
/** @var ActiveForm $form */

$this->title = '用户登录';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-login">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>请在下方输入你的用户凭证:</p>

    <div class="row">
        <div class="col-lg-5">
            <?php $form = ActiveForm::begin();?>

            <?= $form->field($model, 'username')->label('用户名')->textInput(['autofocus' => true]) ?>
            <?= $form->field($model, 'password')->passwordInput()->label('密码') ?>
            <?= $form->field($model, 'rememberMe')->checkbox()->label('记住本次登录') ?>

            <div class="form-group">
                <?= Html::submitButton('登录', ['class' => 'btn btn-primary']) ?>
            </div>
            <div class="form-group">
                <?= Html::a('还没有账户? 点击注册', ['user/register']) ?>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div><!-- user-login -->
