<?php
/* @var $this yii\web\View */

use app\models\SiteConfig;
use yii\bootstrap5\Html;

$this->title = '系统设置';
$siteConfig = new SiteConfig();
$siteConfig->loadFromEnv();
?>

<div class="admin-system">

    <h1><?= Html::encode($this->title) ?></h1>

</div>

