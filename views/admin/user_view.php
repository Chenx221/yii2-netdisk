<?php

use yii\helpers\Html;
use yii\web\YiiAsset;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\User $model */

$this->title = '用户ID: '.$model->id;
$this->params['breadcrumbs'][] = ['label' => '用户管理', 'url' => ['user']];
$this->params['breadcrumbs'][] = $this->title;
$alreadyDisabled = $model->status == 0;
$isCurrentUser = Yii::$app->user->id == $model->id;
$str = $alreadyDisabled ? '启用' : '禁用';
YiiAsset::register($this);
?>
<div class="user-view">

    <h1>用户详情</h1>

    <p>
        <?= Html::a('修改信息', ['user-update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a($str.'用户', ['user-delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => '你确定要'.$str.'这个用户吗?',
                'method' => 'post',
            ],
            'disabled' => $isCurrentUser,
            'title'=> $isCurrentUser ? '不能'.$str.'自己的账户' : '点击'.$str.'用户',
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'username',
            'name',
            'password',
            'auth_key',
            'email:email',
            'status',
            'created_at',
            'last_login',
            'last_login_ip',
            'bio:ntext',
            'role',
            'encryption_key',
            'otp_secret',
            'is_encryption_enabled',
            'is_otp_enabled',
            'storage_limit',
            'recovery_codes',
            'dark_mode',
            'vault_secret',
            'vault_salt',
        ],
    ]) ?>

</div>
