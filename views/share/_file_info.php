<?php
/** @var yii\web\View $this */
/** @var app\models\Share $model */
/** @var bool $isDirectory */

/** @var string $sharerUsername */

use app\assets\FontAwesomeAsset;
use app\utils\FileSizeHelper;
use yii\bootstrap5\Html;
use yii\helpers\Url;
FontAwesomeAsset::register($this);
$this->title = '分享信息';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="share-file-info">

    <h1>
        <i class="fa-solid fa-share-from-square"></i>
        <?= Html::encode($this->title) ?>
    </h1>
    <p><?= ($isDirectory ? '文件夹' : '文件') . ': ' . $model->getShareFileName() ?></p>
    <p>大小: <?= FileSizeHelper::getFormatFSize($model->getAbsoluteFilePath())?></p>
    <p>分享者: <?= Html::encode($sharerUsername) ?></p>  <!-- 显示分享者的用户名 -->
    <p>分享创建日期: <?= Html::encode($model->creation_date) ?></p>

    <p>
        <?php
        if ($isDirectory) {
            // 如果是目录，显示 "下载文件夹" 按钮
            echo Html::a('下载文件夹', Url::to(['share/download', 'share_id' => $model->share_id]), ['class' => 'btn btn-primary']);
        } else {
            // 如果是文件，显示 "下载文件" 按钮
            echo Html::a('下载文件', Url::to(['share/download', 'share_id' => $model->share_id]), ['class' => 'btn btn-primary']);
        }
        ?>    </p>

</div>