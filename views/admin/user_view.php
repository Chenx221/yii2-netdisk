<?php

use app\assets\FontAwesomeAsset;
use app\models\PublicKeyCredentialSourceRepository;
use app\models\User;
use app\utils\FileSizeHelper;
use app\utils\IPLocation;
use kartik\editable\Editable;
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Modal;
use yii\helpers\Html;
use yii\web\YiiAsset;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\User $model */

$this->title = '用户ID: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => '用户管理', 'url' => ['user']];
$this->params['breadcrumbs'][] = $this->title;
$alreadyDisabled = $model->status == 0;
$IPLocation = new IPLocation();
YiiAsset::register($this);
FontAwesomeAsset::register($this);
$this->registerCssFile('@web/css/admin-userv.css');
?>
<div class="user-view">

    <h1>用户详情</h1>

    <p>
        <?= Html::a('禁用二步验证', ['user-totpoff', 'id' => $model->id], [
            'class' => 'btn btn-danger' . ($model->is_otp_enabled == 0 ? ' disabled' : ''),
            'data' => [
                'confirm' => '你确定要取消这个用户的多因素登录吗?',
                'method' => 'post',
            ],
            'title' => '点击取消用户的多因素登录',
        ]) ?>
        <?= Html::button('重置密码', [
            'class' => 'btn btn-danger',
            'data-bs-toggle' => 'modal',
            'data-bs-target' => '#resetPasswordModal',
            'title' => '点击重置用户密码',
        ]) ?>
    </p>


    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            ['attribute' => 'id', 'label' => '用户ID'],
            ['attribute' => 'username', 'label' => '用户名'],
            ['attribute' => 'name', 'label' => '昵称', 'format' => 'raw', 'value' => function ($model) {
                return Editable::widget([
                    'name' => 'name',
                    'asPopover' => true,
                    'value' => $model->name,
                    'header' => '昵称',
                    'size' => 'md',
                    'options' => ['class' => 'form-control', 'placeholder' => '在这里输入新的昵称...'],
                ]);
            }],
            ['attribute' => 'email', 'label' => '电子邮件'],
            ['label' => '头像', 'format' => 'html', 'value' => function ($model) {
                return $model->getGravatar(email: $model->email, s: 100, img: true);
            }],
            ['attribute' => 'status', 'label' => '账户状态', 'format' => 'raw', 'value' => function ($model) {
                if (Yii::$app->user->id == $model->id) {
                    return $model->status == 0 ? '禁用' : '启用';
                }
                return Editable::widget([
                    'name' => 'status',
                    'value' => $model->status == 0 ? '禁用' : '启用',
                    'asPopover' => true,
                    'header' => '账户状态',
                    'format' => Editable::FORMAT_BUTTON,
                    'inputType' => Editable::INPUT_DROPDOWN_LIST,
                    'data' => [0 => '禁用',
                        1 => '启用',],
                    'options' => ['class' => 'form-control'],
                ]);
            }],
            ['attribute' => 'created_at', 'label' => '创建时间', 'value' => function ($model) {
                // 日期时间 (xx天前)
                return $model->created_at . ' (' . Yii::$app->formatter->asRelativeTime($model->created_at) . ')';
            }],
            ['attribute' => 'last_login', 'label' => '最后登录时间', 'value' => function ($model) {
                // 日期时间 (xx天前)
                return $model->last_login . ' (' . Yii::$app->formatter->asRelativeTime($model->last_login) . ')';
            }],
            ['attribute' => 'last_login_ip', 'label' => '上次登录IP', 'value' => function ($model) use ($IPLocation) {
                if (Yii::$app->params['enableIpInfo']) {
                    return $IPLocation->getFormatDetails($model->last_login_ip);
                } else {
                    return $model->last_login_ip;
                }
            }],
            ['attribute' => 'bio', 'label' => '用户简介', 'format' => 'raw', 'value' => function ($model) {
                return Editable::widget([
                    'name' => 'bio',
                    'asPopover' => true,
                    'displayValue' => '查看',
                    'inputType' => Editable::INPUT_TEXTAREA,
                    'value' => $model->bio,
                    'header' => '用户简介',
                    'submitOnEnter' => false,
                    'size' => 'lg',
                    'options' => ['class' => 'form-control', 'rows' => 5]
                ]);
            }],
            ['attribute' => 'role', 'label' => '用户身份', 'value' => function ($model) {
                return $model->role == 'user' ? '用户' : '管理员';
            }],
            ['attribute' => 'is_otp_enabled', 'label' => '多因素登录', 'value' => function ($model) {
                return $model->is_otp_enabled == 0 ? '禁用' : '启用';
            }],
            ['label' => 'Passkey', 'value' => function ($Model) {
                $PKCSR = new PublicKeyCredentialSourceRepository();
                $UserEntitys = $PKCSR->findAllForUserEntity($Model);
                if (empty($UserEntitys)) {
                    return '禁用';
                } else {
                    return '启用';
                }
            }],
            ['label' => '保险箱状态', 'value' => function ($model) {
                if ($model->role == 'admin') {
                    return '不可用';
                }
                return empty($model->vault_secret) ? '未初始化' : '已启用';
            }],
            ['label' => '网盘已用空间', 'value' => function ($model) {
                if ($model->role == 'admin') {
                    return '不可用';
                }
                return FileSizeHelper::formatBytes(FileSizeHelper::getUserHomeDirSize($model->id));
            }],
            ['label' => '保险箱已用空间', 'value' => function ($model) {
                if ($model->role == 'admin') {
                    return '不可用';
                }
                return FileSizeHelper::formatBytes(FileSizeHelper::getUserVaultDirSize($model->id));
            }],
//            ['attribute' => 'storage_limit', 'label' => '存储容量限制', 'value' => function ($model) {
//                if ($model->role == 'admin') {
//                    return '不可用';
//                }
//                return FileSizeHelper::formatMegaBytes($model->storage_limit);
//            }],
            ['attribute' => 'storage_limit', 'label' => '存储容量限制', 'format' => 'raw', 'value' => function ($model) {
                if ($model->role == 'admin') {
                    return '不可用';
                }
                return Editable::widget([
                    'name' => 'storage_limit',
                    'asPopover' => true,
                    'value' => FileSizeHelper::formatMegaBytes($model->storage_limit),
                    'header' => '存储容量限制(最小1MB)',
                    'size' => 'md',
                    'options' => ['class' => 'form-control', 'placeholder' => '在这里输入容量限制(最小值为1MB)...'],
                ]);
            }],
            ['attribute' => 'storage_limit', 'format' => 'html', 'label' => '存储空间使用状态', 'value' => function ($model) {
                if ($model->role == 'admin') {
                    return '不可用';
                }
                return FileSizeHelper::getUsedPercent($model->id) . '<br>' . FileSizeHelper::getFormatUserAllDirSize($model->id) . ' / ' . FileSizeHelper::formatMegaBytes($model->storage_limit);
            }],
        ],
    ])
    ?>
</div>

<?php
Modal::begin([
    'title' => '<h4>重置密码</h4>',
    'id' => 'resetPasswordModal',
    'size' => 'modal-lg',
]);

$form = ActiveForm::begin(['id' => 'reset-password-form', 'action' => ['admin/user-pwdreset'], 'method' => 'post']);

echo $form->field($model, 'id')->hiddenInput()->label(false);
echo $form->field($model, 'password')->passwordInput(['maxlength' => true, 'value' => ''])->label('新密码');

echo Html::submitButton('提交', ['class' => 'btn btn-primary']);

ActiveForm::end();
Modal::end();
?>
