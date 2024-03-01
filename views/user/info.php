<?php
/*
 * 这个页面仿照Windows 11设置中的账户页面设计
 */

/* @var $this yii\web\View */
/* @var $model app\models\User */

use app\assets\FontAwesomeAsset;
use app\utils\IPLocation;
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

$this->title = '个人设置';
FontAwesomeAsset::register($this);
$this->registerCssFile('@web/css/user-info.css');
$details = IPLocation::getDetails($model->last_login_ip);
if (is_null($details)) {
    echo '<script>console.log("IP位置信息获取失败")</script>';
}
?>

<div class="user-info">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="user-profile">
        <?= $model->getGravatar(email: $model->email, s: 100, img: true, atts: ['alt' => 'User Avatar', 'style' => 'border-radius: 50%']) ?>
        <div class="user-details">
            <div class="user-info">
                <p id="p-username"><?= Html::encode($model->username) ?></p>
                <p><?= Html::encode($model->email) ?></p>
                <p>
                    <?php
                    if ($model->role == 'user') {
                        echo '普通用户';
                    } elseif ($model->role == 'admin') {
                        echo '管理员';
                    } else {
                        echo Html::encode($model->role);
                    }
                    ?>
                </p>
            </div>
            <div class="user-login-info">
                <div class="user-last-login">
                    <i class="fa-solid fa-clipboard-user"></i>
                    <div class="login-info-dv">
                        <p class="user-login-info-title">上次登录时间</p>
                        <p class="user-login-info-content"><?= Html::encode($model->last_login) ?></p>
                    </div>
                </div>
                <div class="user-last-login-ip">
                    <i class="fa-solid fa-location-dot"></i>
                    <div class="login-info-dv">
                        <p class="user-login-info-title">上次登录IP</p>
                        <p class="user-login-info-content">
                            <?= Html::encode($model->last_login_ip) ?>
                            (<?= Html::encode(($details === null) ? '' : ($details->bogon ? ('Bogon IP') : ($details->city . ', ' . $details->country))) ?>)
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php $form = ActiveForm::begin(); ?>

    <!--    --><?php //= $form->field($model, 'username')->textInput(['maxlength' => true]) ?>

    <!--    --><?php //= $form->field($model, 'password')->passwordInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'bio')->textarea(['rows' => 6]) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>