<?php
//这个页面部分仿照Windows 11设置中的账户页面设计

/* @var $this yii\web\View */
/* @var $model app\models\User */
/* @var $usedSpace int */
/* @var $vaultUsedSpace int */
/* @var $storageLimit int */
/* @var $focus string */
/* @var $totp_secret string */

/* @var $totp_url string */

/* @var $is_otp_enabled bool */

use app\assets\FontAwesomeAsset;
use app\models\User;
use app\utils\FileSizeHelper;
use app\utils\IPLocation;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;
use yii\bootstrap5\Modal;
use yii\helpers\Url;
use yii\web\JqueryAsset;
use yii\web\View;

$this->title = '个人设置';
FontAwesomeAsset::register($this);
JqueryAsset::register($this);
$this->registerCssFile('@web/css/user-info.css');
$details = IPLocation::getDetails($model->last_login_ip); // IP LOCATION

// 容量计算
$usedSpace_F = FileSizeHelper::formatBytes($usedSpace); //网盘已用空间 格式化文本
$vaultUsedSpace_F = FileSizeHelper::formatBytes($vaultUsedSpace); //保险箱已用空间 格式化文本
$storageLimit_F = FileSizeHelper::formatMegaBytes($storageLimit); //存储限制 格式化文本
$totalUsed_F = FileSizeHelper::formatBytes($usedSpace + $vaultUsedSpace); //总已用空间 格式化文本
$is_unlimited = ($storageLimit === -1); //检查是否为无限制容量
$usedPercent = $is_unlimited ? 0 : round($usedSpace / ($storageLimit * 1024 * 1024) * 100); //网盘已用百分比
$vaultUsedPercent = $is_unlimited ? 0 : round($vaultUsedSpace / ($storageLimit * 1024 * 1024) * 100); //保险箱已用百分比
$totalUsedPercent = min(($usedPercent + $vaultUsedPercent), 100); //总已用百分比

// QR-CODE
if (!is_null($totp_secret)) {
    $writer = new PngWriter();
    $qrCode = QrCode::create($totp_url)
        ->setEncoding(new Encoding('UTF-8'))
        ->setErrorCorrectionLevel(ErrorCorrectionLevel::Low)
        ->setSize(300)
        ->setMargin(10)
        ->setRoundBlockSizeMode(RoundBlockSizeMode::Margin)
        ->setForegroundColor(new Color(0, 0, 0))
        ->setBackgroundColor(new Color(255, 255, 255));
    $result = $writer->write($qrCode);
}

// totp
$user = new User();
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
                                <?= Html::encode(($details === null) ? '' : '(' . ($details->bogon ? ('Bogon IP') : ($details->city . ', ' . $details->country)) . ')') ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="accordion userAccordion" id="userAccordion">
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingStorage">
                    <button class="accordion-button <?= ($focus === 'storage' || $focus === null) ? '' : 'collapsed' ?>"
                            type="button" data-bs-toggle="collapse"
                            data-bs-target="#collapseStorage" <?= ($focus === 'storage' || $focus === null) ? 'aria-expanded="true"' : '' ?>>
                    <span class="accordion-storage-content">
                        <span>
                            <i class="fa-solid fa-hard-drive"></i>
                            <span>存储空间</span>
                        </span>
                        <span style="margin-right: 20px">
                            <?= $totalUsed_F . ' / ' . $storageLimit_F ?>
                        </span>
                    </span>
                    </button>
                </h2>
                <div id="collapseStorage"
                     class="accordion-collapse collapse  <?= ($focus === 'storage' || $focus === null) ? 'show' : '' ?>">
                    <div class="accordion-body">
                        <div class="storage-info">
                            <div class="storage-columns">
                                <div class="storage-usage" style="width: 27%">
                                    <p>当前已使用容量</p>
                                    <span style="font-weight: 600;font-size: 1.4rem;color: black;padding-left: 2px;"><?= $totalUsed_F ?>
                                </span>
                                    <span style="font-size: 0.9rem;">/ <?= $storageLimit_F ?></span>
                                </div>
                                <div style="width: 47%">
                                    <p><?= $totalUsedPercent ?>% 已用</p>
                                    <div class="progress">
                                        <div class="progress-bar" role="progressbar"
                                             style="width: <?= $usedPercent ?>%;background-color: rgb(52,131,250)"
                                             aria-valuenow="<?= $usedPercent ?>"
                                             aria-valuemin="0" aria-valuemax="100"></div>
                                        <div class="progress-bar" role="progressbar"
                                             style="width: <?= $vaultUsedPercent ?>%;background-color: rgb(196,134,0)"
                                             aria-valuenow="<?= $vaultUsedPercent ?>"
                                             aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <div class="storage-legend" style="color: rgb(140,139,139)">
                                        <div class="legend-item">
                                            <span class="legend-color"
                                                  style="background-color: rgb(52,131,250);"></span>
                                            <span>网盘已用空间</span>
                                            <span style="margin-left: auto;"><?= $usedSpace_F ?>
                                                <?= Html::a('<i class="fa-solid fa-arrow-up-right-from-square" style="font-size: 0.75rem;"></i>', ['home/index']) ?>
                                        </span>
                                        </div>
                                        <div class="legend-item">
                                            <span class="legend-color" style="background-color: rgb(196,134,0);"></span>
                                            <span>保险箱已用空间</span>
                                            <span style="margin-left: auto;"><?= $vaultUsedSpace_F ?>
<!--                                            PENDING-->
                                            <?= Html::a('<i class="fa-solid fa-arrow-up-right-from-square" style="font-size: 0.75rem;"></i>', ['site/index']) ?>
                                        </span>
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
                    <button class="accordion-button <?= ($focus === 'bio') ? '' : 'collapsed' ?>" type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#collapseBio" <?= ($focus === 'bio') ? 'aria-expanded="true"' : '' ?>>
                        <i class="fa-solid fa-address-card"></i>
                        <span>个人简介</span>
                    </button>
                </h2>
                <div id="collapseBio" class="accordion-collapse collapse  <?= ($focus === 'bio') ? 'show' : '' ?>">
                    <div class="accordion-body">
                        <?php $form = yii\widgets\ActiveForm::begin(); ?>
                        <?= $form->field($model, 'bio')->textarea(['rows' => 6])->label('简介') ?>
                        <div class="form-group">
                            <?= yii\helpers\Html::submitButton('保存', ['class' => 'btn btn-success']) ?>
                        </div>
                        <?php yii\widgets\ActiveForm::end(); ?>
                    </div>
                </div>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingPassword">
                    <button class="accordion-button <?= ($focus === 'password') ? '' : 'collapsed' ?>" type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#collapsePassword" <?= ($focus === 'password') ? 'aria-expanded="true"' : '' ?>>
                        <i class="fa-solid fa-key"></i>
                        <span>修改密码</span>
                    </button>
                </h2>
                <div id="collapsePassword"
                     class="accordion-collapse collapse <?= ($focus === 'password') ? 'show' : '' ?>">
                    <div class="accordion-body">
                        <?php $form = ActiveForm::begin([
                            'action' => Url::to(['user/change-password']),
                            'method' => 'post'
                        ]); ?>
                        <?= $form->field($model, 'oldPassword')->passwordInput()->label('原密码') ?>
                        <?= $form->field($model, 'newPassword')->passwordInput()->label('新密码') ?>
                        <?= $form->field($model, 'newPasswordRepeat')->passwordInput()->label('重复新密码') ?>
                        <div class="form-group">
                            <?= Html::submitButton('修改密码', ['class' => 'btn btn-success']) ?>
                        </div>
                        <?php ActiveForm::end(); ?>
                    </div>
                </div>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingAdvanced">
                    <button class="accordion-button <?= ($focus === 'advanced') ? '' : 'collapsed' ?>" type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#collapseAdvanced" <?= ($focus === 'advanced') ? 'aria-expanded="true"' : '' ?>>
                        <i class="fa-solid fa-flask"></i>
                        <span>高级功能</span>
                    </button>
                </h2>
                <div id="collapseAdvanced"
                     class="accordion-collapse collapse <?= ($focus === 'advanced') ? 'show' : '' ?>">
                    <div class="accordion-body">
                        <!--TODO:二步验证、passwordless-->
                        <h4>二步验证</h4>
                        <hr>
                        <p>使用除您密码之外的第二种方法来增强您账号的安全性。</p>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">
                                <h5>
                                    <i class="fa-solid fa-shield-halved"></i>
                                    TOTP (Authenticator app)
                                </h5>
                                <div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch"
                                               id="totp-enabled" <?= $is_otp_enabled ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="totp-enabled" data-bs-toggle="modal"
                                               data-bs-target="#totpSetupModal">启用 TOTP</label>
                                    </div>
                                </div>
                            </li>
                            <li class="list-group-item">
                                <h5>
                                    <i class="fa-solid fa-user-lock"></i>
                                    备用码
                                </h5>
                                <div>
                                    <?= Html::a('获取恢复代码(请妥善保存)', Url::to(['user/download-recovery-codes']), ['class' => 'btn btn-outline-primary btn-sm', 'id' => 'generate-backup-codes']) ?>
                                </div>
                            </li>
                        </ul>
                        <br>

                        <h4>无密码认证</h4>
                        <hr>
                        <p>遵循 FIDO2 标准为无密码身份验证设置您的账号。</p>
                        <br>

                        <h4>主题</h4>
                        <hr>
                        <p>可以在</p>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="useDarkTheme">
                            <label class="form-check-label" for="useDarkTheme">启用夜间模式</label>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="autoTheme">
                            <label class="form-check-label" for="autoTheme">Auto</label>
                        </div>
                        <br>

                        <h4>删除账户</h4>
                        <hr>
                        <p>这个操作不支持撤回，请谨慎操作。</p>
                        <button type="button" class="btn btn-danger" data-bs-toggle="modal"
                                data-bs-target="#deleteAccountModal">
                            删除账户
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php
Modal::begin([
    'title' => '<h4>确定？</h4>',
    'id' => 'deleteAccountModal',
    'size' => 'modal-sm',
]);

echo Html::tag('div', '确定要删除这个账户？', ['class' => 'modal-body']);

echo Html::beginForm(['user/delete'], 'post', ['id' => 'delete-form']);

echo '<div>';
echo Html::checkbox('deleteConfirm', false, ['label' => '确认', 'id' => 'deleteConfirm']);
echo '</div>';

echo '<div class="text-end">';
echo Html::submitButton('继续删除', ['class' => 'btn btn-danger', 'disabled' => true, 'id' => 'deleteButton']);
echo '</div>';

echo Html::endForm();

Modal::end();

Modal::begin([
    'title' => '<h4>需要进一步操作以启用二步验证</h4>',
    'id' => 'totpSetupModal',
    'size' => 'model-xl',
]);
/*<img src="<?= is_null($totp_secret) ? '' : $result->getDataUri() ?>" alt="qrcode"/>*/
?>
    <div class="row">
        <div class="col-md-6 text-center center">
            <img src="<?= is_null($totp_secret) ? '' : $result->getDataUri() ?>" alt="QR Code" class="img-fluid">
        </div>
        <div class="col-md-6">
            <p>使用兼容TOTP的应用程序扫描左侧二维码以添加二步验证</p>
            <p>推荐以下二步验证器：:</p>
            <ul>
                <li>
                    <a href="https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2&hl=en_US">Google
                        Authenticator</a></li>
                <li><a href="https://play.google.com/store/apps/details?id=com.azure.authenticator&hl=en_US">Microsoft
                        Authenticator</a></li>
                <li><a href="https://play.google.com/store/apps/details?id=com.authy.authy&hl=en">Authy</a></li>
                <!--这是广告吗-->
                <li><a href="https://git.chenx221.cyou/chenx221/OTP/releases">自制TOTP验证器(Windows)</a></li>
                <!-- Add more applications as needed -->
            </ul>
            <div class="input-group mb-3">
                <label for="totp_secret">无法扫描?使用下面的密钥来添加</label>
                <input type="text" class="form-control" value="<?= $totp_secret ?>" id="totp_secret" readonly>
                <button class="btn btn-outline-secondary" type="button"
                        onclick="navigator.clipboard.writeText('<?= $totp_secret ?>')">Copy
                </button>
            </div>
            <?php
            if (!$is_otp_enabled) {
                $form = ActiveForm::begin([
                    'action' => ['user/setup-two-factor'],
                    'method' => 'post'
                ]);
                echo Html::activeHiddenInput($user, 'otp_secret', ['value' => $totp_secret]);
                echo $form->field($user, 'totp_input')->textInput()->label('最后一步! 输入TOTP应用程序上显示的密码以启用二步验证');
                echo Html::submitButton('启用二步验证', ['class' => 'btn btn-primary']);
                ActiveForm::end();
            }
            ?>
        </div>
    </div>
<?php

Modal::end();
$this->registerJsFile('@web/js/user-info.js', ['depends' => [JqueryAsset::class], 'position' => View::POS_END]);
?>