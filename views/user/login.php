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

    <p>请在下方输入你的用户名:</p>

    <div class="row">
        <div class="col-lg-5">
            <?php $form = ActiveForm::begin(); ?>

            <?= $form->field($model, 'username')->label('用户名')->textInput(['autofocus' => true]) ?>
            <div class="form-group">
                <?= Html::submitButton('下一步', ['class' => 'btn btn-primary']) ?>
            </div>
            <?php ActiveForm::end(); ?>
            <div class="form-group">
                <?= Html::a('还没有账户? 去注册', ['user/register']) ?>
            </div>
        </div>
    </div>
</div><!-- user-login -->