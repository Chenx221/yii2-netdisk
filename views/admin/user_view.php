<?php

use app\assets\FontAwesomeAsset;
use app\models\PublicKeyCredentialSourceRepository;
use app\utils\FileSizeHelper;
use app\utils\IPLocation;
use kartik\editable\Editable;
use yii\helpers\Html;
use yii\web\YiiAsset;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\User $model */

$this->title = '用户ID: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => '用户管理', 'url' => ['user']];
$this->params['breadcrumbs'][] = $this->title;
$alreadyDisabled = $model->status == 0;
$isCurrentUser = Yii::$app->user->id == $model->id ? 'disabled' : '';
$str = $alreadyDisabled ? '启用' : '禁用';
$IPLocation = new IPLocation();
YiiAsset::register($this);
FontAwesomeAsset::register($this);
?>
<div class="user-view">

    <h1>用户详情</h1>

    <p>
        <!--        --><?php //= Html::a('修改信息', ['user-update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a($str . '用户', ['user-delete', 'id' => $model->id], [
            'class' => 'btn btn-danger ' . $isCurrentUser,
            'data' => [
                'confirm' => '你确定要' . $str . '这个用户吗?',
                'method' => 'post',
            ],
            'title' => '点击' . $str . '用户',
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
                    'asPopover' => false,
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
//                return $model->status == 0 ? '禁用' : '启用';
                //TODO 未完成
                return Editable::widget([
                    'name' => 'status',
                    'asPopover' => true,
                    'header' => '账户状态',
                    'format' => Editable::FORMAT_BUTTON,
                    'inputType' => Editable::INPUT_DROPDOWN_LIST,
                    'data' => [1,2,3], // any list of values
                    'options' => ['class' => 'form-control'],
                    'editableValueOptions' => ['class' => 'text-danger']
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
            ['attribute' => 'bio', 'label' => '用户简介'],
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
            ['attribute' => 'storage_limit', 'label' => '存储容量限制', 'value' => function ($model) {
                if ($model->role == 'admin') {
                    return '不可用';
                }
                return FileSizeHelper::formatMegaBytes($model->storage_limit);
            }],
            ['attribute' => 'storage_limit', 'format' => 'html', 'label' => '存储空间使用状态', 'value' => function ($model) {
                if ($model->role == 'admin') {
                    return '不可用';
                }
                return FileSizeHelper::getUsedPercent($model->id) . '<br>' . FileSizeHelper::getFormatUserAllDirSize($model->id) . ' / ' . FileSizeHelper::formatMegaBytes($model->storage_limit);
            }],
        ],
    ]) ?>

</div>
