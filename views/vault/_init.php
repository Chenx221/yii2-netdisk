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

    <div class="row">
        <div class="col-lg-5">
            <?php $form = ActiveForm::begin(); ?>
            <?= $form->field($model, 'input_vault_secret')->label('保险箱密码(建议不要与登陆密码相同)')->passwordInput(['autofocus' => true]) ?>
            <div class="form-group">
                <?= Html::submitButton('初始化保险箱', ['class' => 'btn btn-primary']) ?>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
