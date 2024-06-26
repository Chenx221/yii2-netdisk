<?php

/** @var yii\web\View $this */

/** @var string $content */

use app\assets\AppAsset;
use app\widgets\Alert;
use yii\bootstrap5\Breadcrumbs;
use yii\bootstrap5\Html;
use yii\bootstrap5\Nav;
use yii\bootstrap5\NavBar;
use yii\web\View;

AppAsset::register($this);

$this->registerCsrfMetaTags();
$this->registerMetaTag(['charset' => Yii::$app->charset], 'charset');
$this->registerMetaTag(['name' => 'viewport', 'content' => 'width=device-width, initial-scale=1, shrink-to-fit=no']);
$this->registerMetaTag(['name' => 'description', 'content' => $this->params['meta_description'] ?? '']);
$this->registerMetaTag(['name' => 'keywords', 'content' => $this->params['meta_keywords'] ?? '']);
$this->registerLinkTag(['rel' => 'icon', 'type' => 'image/x-icon', 'href' => Yii::getAlias('@web/favicon.ico')]);
$darkMode = Yii::$app->user->isGuest ? 0 : Yii::$app->user->identity->dark_mode;
$this->registerCssFile('@web/css/fuckyou-navpadding.css');
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" class="h-100" <?= $darkMode === 1 ? 'data-bs-theme="dark"' : '' ?> >
<head>
    <title><?= Html::encode($this->title) . ' | ' . Yii::$app->name ?></title>
    <?php $this->head() ?>
    <?php
    if ($_ENV['CLARITY_ENABLED'] === 'true') {
        $clarityId = $_ENV['CLARITY_ID'];
        echo <<<EOL
            <script type="text/javascript">
                (function(c,l,a,r,i,t,y){
                    c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};
                    t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;
                    y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);
                })(window, document, "clarity", "script", "$clarityId");
            </script>
        EOL;
    }
    if ($_ENV['GA_ENABLED'] === 'true') {
        $gaId = $_ENV['GA_ID'];
        echo <<<EOL
            <!-- Google tag (gtag.js) -->
            <script async src="https://www.googletagmanager.com/gtag/js?id=$gaId"></script>
            <script>
              window.dataLayer = window.dataLayer || [];
              function gtag(){dataLayer.push(arguments);}
              gtag('js', new Date());
            
              gtag('config', '$gaId');
            </script>
        EOL;
    }
    ?>
</head>
<body class="d-flex flex-column h-100">
<?php $this->beginBody() ?>

<header id="header">
    <?php
    NavBar::begin([
        'brandLabel' => Yii::$app->name,
        'brandUrl' => Yii::$app->homeUrl,
        'options' => ['class' => 'navbar-expand-md navbar-dark bg-dark fixed-top']
    ]);
    echo Nav::widget([
        'options' => ['class' => 'navbar-nav'],
        'items' => [
            ['label' => '首页', 'url' => ['/site/index']],
            ['label' => '管理', 'items' => [
                ['label' => '用户管理', 'url' => ['/admin/user']],
                ['label' => '文件分享管理', 'url' => ['/admin/share-manage']],
                ['label' => '文件收集管理', 'url' => ['/admin/collection-manage']],
                ['label' => '站点公告管理', 'url' => ['/admin/announcements-manage']],
                ['label' => '工单支持管理', 'url' => ['/admin/ticket-manage']],
            ],
            ],
            ['label' => '日志', 'items' => [
                ['label' => '用户登录日志', 'url' => ['/admin/login-log']],
                ['label' => '分享访问日志', 'url' => ['/admin/access-log']],
                ['label' => '文件收集上传日志', 'url' => ['/admin/collection-up-log']],
            ],
            ],
            ['label' => '设置', 'items' => [
                ['label' => '个人设置', 'url' => ['/admin/info']],
                ['label' => '系统设置', 'url' => ['/admin/system']],
                ['label' => '系统信息', 'url' => ['/admin/sysinfo']],
            ],
            ],
            '<li class="nav-item">'
            . Html::beginForm(['/user/logout'])
            . Html::submitButton(
                '登出账户 (' . Yii::$app->user->identity->username . ')',
                ['class' => 'nav-link btn btn-link logout']
            )
            . Html::endForm()
            . '</li>'
        ]
    ]);
    NavBar::end();
    ?>
</header>

<main id="main" class="flex-shrink-0" role="main">
    <div class="container">
        <?php if (!empty($this->params['breadcrumbs'])): ?>
            <?= Breadcrumbs::widget(['links' => $this->params['breadcrumbs']]) ?>
        <?php endif ?>
        <?= Alert::widget() ?>
        <?= $content ?>
    </div>
</main>

<footer id="footer" class="mt-auto py-3">
    <div class="container">
        <div class="row text-muted">
            <div class="col-md-6 text-center text-md-start"><?php echo '&copy; Created & Design by ' . '<a href="https://blog.chenx221.cyou" rel="external">Chenx221</a> | 2024 - ' . date('Y') ?></div>
            <div class="col-md-6 text-center text-md-end"><?= Yii::t('yii', 'Powered by {yii}', [
                    'yii' => '<a href="https://www.yiiframework.com/" rel="external">' . Yii::t('yii',
                            'Yii Framework') . '</a>',
                ]) ?></div>
        </div>
    </div>
</footer>

<?php $this->endBody() ?>
</body>
</html>
<?php
if ($darkMode === 2) {
    $this->registerJsFile('@web/js/darkmode_auto.js', ['position' => View::POS_BEGIN]);
} else if ($darkMode === 1) {
    $this->registerJsFile('@web/js/darkmode.js', ['position' => View::POS_BEGIN]);
}
?>
<?php $this->endPage() ?>

