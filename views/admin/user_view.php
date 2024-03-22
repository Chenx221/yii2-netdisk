<?php

use yii\helpers\Html;
use yii\web\YiiAsset;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\User $model */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Users', 'url' => ['user']];
$this->params['breadcrumbs'][] = $this->title;
YiiAsset::register($this);
?>
<div class="user-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['user-update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['user-delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
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
