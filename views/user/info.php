<?php
//这个页面仿照Windows 11设置中的账户页面设计

/* @var $this yii\web\View */

/* @var $model app\models\User */

use app\assets\FontAwesomeAsset;
use app\utils\IPLocation;
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
                        <p class="user-login-info-content"><?= Html::encode($model->last_login . ' (CST)') ?></p>
                    </div>
                </div>
                <div class="user-last-login-ip">
                    <i class="fa-solid fa-location-dot"></i>
                    <div class="login-info-dv">
                        <p class="user-login-info-title">上次登录IP</p>
                        <p class="user-login-info-content">
                            <?= Html::encode($model->last_login_ip) ?>
                            (<?= Html::encode(($details === null) ? '' : ($details->bogon ? ('Bogon IP') : ($details->city . ', ' . $details->country))) ?>
                            )
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="accordion userAccordion" id="userAccordion">
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingStorage">
                <button class="accordion-button" type="button" data-bs-toggle="collapse"
                        data-bs-target="#collapseStorage" aria-expanded="true">
                    <span class="accordion-storage-content">
                        <span>
                            <i class="fa-solid fa-hard-drive"></i>
                            <span>存储空间</span>
                        </span>
                        <span style="margin-right: 20px">
                            N/A <!-- 这里是你的容量显示 -->
                        </span>
                    </span>
                </button>
            </h2>
            <div id="collapseStorage" class="accordion-collapse collapse show">
                <div class="accordion-body">
                    <div class="storage-info">
                        <div class="storage-columns">
                            <div class="storage-usage" style="width: 27%">
                                <p>当前已使用容量</p>
                                <span style="font-weight: 600;font-size: 1.4rem;color: black;padding-left: 2px;">N/A GB</span>
                                <span style="font-size: 0.9rem;">/ N/A TB</span>
                            </div>
                            <div style="width: 47%">
                                <p>N/A% 已用</p>
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar"
                                         style="width: 25%;background-color: rgb(52,131,250)" aria-valuenow="25"
                                         aria-valuemin="0" aria-valuemax="100"></div>
                                    <div class="progress-bar" role="progressbar"
                                         style="width: 25%;background-color: rgb(196,134,0)" aria-valuenow="25"
                                         aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <div class="storage-legend" style="color: rgb(140,139,139)">
                                    <div class="legend-item">
                                        <span class="legend-color" style="background-color: rgb(52,131,250);"></span>
                                        <span>网盘已用空间</span>
                                    </div>
                                    <div class="legend-item">
                                        <span class="legend-color" style="background-color: rgb(196,134,0);"></span>
                                        <span>保险箱已用空间</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingBio">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                        data-bs-target="#collapseBio">
                    <i class="fa-solid fa-address-card"></i>
                    <span>个人简介</span>
                </button>
            </h2>
            <div id="collapseBio" class="accordion-collapse collapse">
                <div class="accordion-body">
                    <!-- 修改bio相关内容 -->
                </div>
            </div>
        </div>
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingPassword">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                        data-bs-target="#collapsePassword">
                    <i class="fa-solid fa-key"></i>
                    <span>修改密码</span>
                </button>
            </h2>
            <div id="collapsePassword" class="accordion-collapse collapse">
                <div class="accordion-body">
                    <!-- 修改密码表单相关内容 -->
                </div>
            </div>
        </div>
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingAdvanced">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                        data-bs-target="#collapseAdvanced">
                    <i class="fa-solid fa-flask"></i>
                    <span>高级选项</span>
                </button>
            </h2>
            <div id="collapseAdvanced" class="accordion-collapse collapse">
                <div class="accordion-body">
                    <!-- 高级选项相关内容 -->
                </div>
            </div>
        </div>
    </div>

</div>