<?php
// username+fido verify
use app\assets\SimpleWebAuthnBrowser;
use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Url;
use yii\web\JqueryAsset;
use yii\web\View;

/** @var yii\web\View $this */
/** @var app\models\User $model */
/** @var ActiveForm $form */
JqueryAsset::register($this);
SimpleWebAuthnBrowser::register($this);
$this->title = '用户登录';
$this->params['breadcrumbs'][] = $this->title;
?>
    <div class="user-login">
        <h1><?= Html::encode($this->title) ?></h1>

        <p>我们已经找到了你的账户，请在下方验证Passkey以继续:</p>
        <div class="alert alert-success" role="alert" hidden>
            <span id="webauthn_success"></span>
        </div>
        <div class="alert alert-danger" role="alert" hidden>
            <span id="webauthn_error"></span>
        </div>
        <div class="row">
            <div class="col-lg-5">
                <?php $form = ActiveForm::begin(['action' => '#']); ?>
                <?= $form->field($model, 'username')->label('用户名')->textInput(['autofocus' => true, 'readonly' => true, 'id' => 'username']) ?>
                <?= $form->field($model, 'rememberMe')->checkbox(['id' => 'rememberMe'])->label('记住本次登录') ?>
                <div class="form-group">
                    <?= Html::button('登录', ['class' => 'btn btn-primary', 'id' => 'webauthn_verify']) ?>
                </div>
                <?php ActiveForm::end(); ?>
                <div class="form-group">
                    <?= Html::a('不想使用Passkey? 回到传统登录方式', Url::to(['user/login', 'username' => $model->username])) ?>
                </div>
            </div>
        </div>
    </div><!-- user-login -->
<?php
$this->registerJsFile('@web/js/login-core.js', ['depends' => [JqueryAsset::class], 'position' => View::POS_END]);
?>