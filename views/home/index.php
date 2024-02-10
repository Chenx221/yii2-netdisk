<?php

/* @var $this yii\web\View */
/* @var $directoryContents array 文件和文件夹内容数组 */
/* @var $parentDirectory string 父目录 */

/* @var $directory string 当前路径 */

use yii\bootstrap5\Html;

$this->title = '文件管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="home-directory">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('HOME', ['home/index']) ?>
        <?php if ($directory !== null): ?>
            <?php
            $parts = explode('/', $directory);
            $path = '';
            foreach ($parts as $part):
                $path .= $part;
                echo ' / ' . Html::a($part, ['home/index', 'directory' => $path]);
                $path .= '/';
            endforeach;
            ?>
        <?php endif; ?>
    </p>

    <ul>
        <?php foreach ($directoryContents as $item): ?>
            <?php $relativePath = $directory ? $directory . '/' . $item : $item; ?>
            <!-- 修复错误的绝对路径问题-->
            <?php $absolutePath = Yii::getAlias('@app') . '/data/' . Yii::$app->user->id . '/' . $relativePath; ?>
            <?php if (is_dir($absolutePath)): ?>
                <!-- 文件夹 -->
                <li><?= Html::a($item, ['home/index', 'directory' => $relativePath]) ?></li>
            <?php else: ?>
                <!-- 文件 -->
                <li><?= Html::a($item, ['home/download', 'relativePath' => $relativePath]) ?>
                    (<?= Html::a('下载', ['home/download', 'relativePath' => $relativePath]) ?>)
                </li>
            <?php endif; ?>
        <?php endforeach; ?>
    </ul>
</div>