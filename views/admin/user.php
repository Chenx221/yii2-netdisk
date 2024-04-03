<?php

use app\models\PublicKeyCredentialSourceRepository;
use app\models\User;
use app\utils\FileSizeHelper;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var app\models\UserSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
$this->title = '用户管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('添加用户', ['user-create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php Pjax::begin(); ?>
    <div class="table-responsive">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'columns' => [
                ['attribute' => 'id', 'label' => 'ID'],
                ['attribute' => 'username', 'label' => '用户名'],
                ['attribute' => 'name', 'label' => '昵称'],
                ['attribute' => 'email', 'format' => 'email', 'label' => '电子邮箱'],
                ['attribute' => 'status', 'label' => '账户启用', 'value' => function ($model) {
                    return $model->status == 0 ? '禁用' : '启用';
                }, 'filter' => ['0' => '禁用', '1' => '启用']],
                ['attribute' => 'created_at', 'label' => '账户创建时间', 'filter' => false],
                ['attribute' => 'last_login', 'label' => '上次登陆时间', 'filter' => false],
                ['attribute' => 'last_login_ip', 'label' => '上次登录IP'],
                ['attribute' => 'role', 'label' => '用户身份', 'value' => function ($model) {
                    return $model->role == 'user' ? '用户' : '管理员';
                }, 'filter' => ['user' => '用户', 'admin' => '管理员']],
                ['attribute' => 'is_otp_enabled', 'label' => '多因素登录', 'value' => function ($model) {
                    return $model->is_otp_enabled == 0 ? '禁用' : '启用';
                }, 'filter' => ['0' => '禁用', '1' => '启用']],
                ['label' => 'Passkey', 'value' => function ($Model) {
                    $PKCSR = new PublicKeyCredentialSourceRepository();
                    $UserEntitys = $PKCSR->findAllForUserEntity($Model);
                    if (empty($UserEntitys)) {
                        return '禁用';
                    }else{
                        return '启用';
                    }
                }],
                ['attribute' => 'storage_limit', 'label' => '空间使用情况', 'value' => function ($model) {
                    if ($model->role == 'user') {
                        return FileSizeHelper::getFormatUserAllDirSize($model->id) . ' / ' . FileSizeHelper::formatMegaBytes($model->storage_limit);
                    } else {
                        return '不可用';
                    }
                }, 'filter' => false],
                [
                    'class' => ActionColumn::class,
                    'header' => '操作',
                    'template' => '{view}',
                    'urlCreator' => function ($action, User $model, $key, $index, $column) {
                        return Url::toRoute(['user-' . $action, 'id' => $model->id]);
                    }
                ],
            ],
        ]); ?>
    </div>

    <?php Pjax::end(); ?>

</div>
