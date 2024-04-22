<?php
// username+password verify
use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Url;


/** @var yii\web\View $this */
/** @var app\models\User $model */
/** @var ActiveForm $form */
$this->title = '用户登录';
$this->params['breadcrumbs'][] = $this->title;
$verifyProvider = Yii::$app->params['verifyProvider'];
if ($verifyProvider === 'reCAPTCHA') {
    $this->registerJsFile('https://www.recaptcha.net/recaptcha/api.js?hl=zh-CN', ['async' => true, 'defer' => true]);
} elseif ($verifyProvider === 'hCaptcha') {
    $this->registerJsFile('https://js.hcaptcha.com/1/api.js?hl=zh-CN', ['async' => true, 'defer' => true]);
} elseif ($verifyProvider === 'Turnstile') {
    $this->registerJsFile('https://challenges.cloudflare.com/turnstile/v0/api.js', ['async' => true, 'defer' => true]);
} ?>
<div class="user-login">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>我们已经找到了你的账户，请在下方输入你的密码以继续:</p>

    <div class="row">
        <div class="col-lg-5">
            <?php $form = ActiveForm::begin(['action' => [Url::to('user/login')]]); ?>

            <?= $form->field($model, 'username')->label('用户名')->textInput(['autofocus' => true, 'readonly' => true]) ?>
            <?= $form->field($model, 'password')->passwordInput()->label('密码') ?>
            <?= $form->field($model, 'rememberMe')->checkbox()->label('记住本次登录') ?>
            <div class="form-group">
                <?php if ($verifyProvider === 'reCAPTCHA'): ?>
                    <div class="g-recaptcha" data-sitekey="<?= Yii::$app->params['reCAPTCHA']['siteKey'] ?>"></div>
                <?php elseif ($verifyProvider === 'hCaptcha'): ?>
                    <div class="h-captcha" data-sitekey="<?= Yii::$app->params['hCaptcha']['siteKey'] ?>"></div>
                <?php elseif ($verifyProvider === 'Turnstile'): ?>
                    <div class="cf-turnstile" data-sitekey="<?= Yii::$app->params['Turnstile']['siteKey'] ?>"
                         data-language="zh-cn"></div>
                <?php endif; ?>
            </div>
            <div class="form-group">
                <?= Html::submitButton('登录', ['class' => 'btn btn-primary']) ?>
            </div>
            <?php ActiveForm::end(); ?>
            <div class="form-group">
                <?= Html::a('回到上个页面', [Url::to('user/login')]) ?>
            </div>
        </div>
    </div>
</div><!-- user-login -->