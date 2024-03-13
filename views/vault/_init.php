<?php

use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\User $model */
/** @var ActiveForm $form */

$this->title = '初始化文件保险箱';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="vault-init">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>第一次使用文件保险箱，请在下方输入保险箱密码:</p>
    <p>
        请牢记设置的保险箱密码，保险箱内所有文件都会使用此密码进行端到端加密，只有拥有正确密码的用户才可以解密文件(服务端也无法查看文件内容)
    </p>

    <div class="row">
        <div class="col-lg-5">
            <?php $form = ActiveForm::begin(['id' => 'init-vault-form', 'action' => ['vault/init'], 'method' => 'post']); ?>
            <?= $form->field($model, 'input_vault_secret')->label('保险箱密码:')->passwordInput(['autofocus' => true]) ?>
            <div class="form-group">
                <?= Html::submitButton('初始化保险箱', ['class' => 'btn btn-primary']) ?>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
