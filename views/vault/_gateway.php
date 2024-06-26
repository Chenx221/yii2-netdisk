<?php

use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;
use yii\web\View;

/** @var yii\web\View $this */
/** @var app\models\User $model */
/** @var ActiveForm $form */

$this->title = '解锁文件保险箱';
$this->params['breadcrumbs'][] = $this->title;
?>
    <div class="vault-gateway">
        <h1><?= Html::encode($this->title) ?></h1>

        <p>要访问文件保险箱，你必须要提供正确的保险箱密码</p>

        <div class="row">
            <div class="col-lg-5">
                <?php $form = ActiveForm::begin(['id' => 'gateway-vault-form', 'action' => ['vault/auth'], 'method' => 'post']); ?>
                <?= $form->field($model, 'vault_secret')->passwordInput(['id'=>'password'])->label('保险箱密码(不是登陆密码)') ?>
                <div class="form-group">
                    <?= Html::submitButton('确认', ['class' => 'btn btn-primary']) ?>
                </div>
                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
<?php
$this->registerJsFile('@web/js/vault_gateway_hook.js', ['position' => View::POS_END]);
?>