<?php

/* @var $this yii\web\View */
/* @var $directoryContents array 文件和文件夹内容数组 */
/* @var $parentDirectory string 父目录 */
/* @var $directory string 当前路径 */

use yii\bootstrap5\Html;
use app\assets\FontAwesomeAsset;

$this->title = '文件管理';
$this->params['breadcrumbs'][] = $this->title;
FontAwesomeAsset::register($this);
?>
<div class="home-directory">
    <h1><?= Html::encode($this->title) ?></h1>

    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <?= Html::a('HOME', ['home/index'], ['class' => 'breadcrumb-item']) ?>
            <?php if ($directory !== null): ?>
                <?php
                $parts = explode('/', $directory);
                $path = '';
                foreach ($parts as $part):
                    $path .= $part;
                    echo Html::a($part, ['home/index', 'directory' => $path], ['class' => 'breadcrumb-item']);
                    $path .= '/';
                endforeach;
                ?>
            <?php endif; ?>
        </ol>
    </nav>

    <table class="table table-hover">
        <thead class="table-light">
        <tr>
            <th scope="col">名称</th>
            <th scope="col">操作</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($directoryContents as $item): ?>
            <?php $relativePath = $directory ? $directory . '/' . $item['name'] : $item['name']; ?>
            <!-- 修复错误的绝对路径问题-->
            <?php $absolutePath = Yii::getAlias('@app') . '/data/' . Yii::$app->user->id . '/' . $relativePath; ?>
            <tr>
                <?php if (is_dir($absolutePath)): ?>
                    <!-- 文件夹 -->
                    <td>
                        <?= Html::tag('i', '', ['class' => $item['type']]) ?>
                        <?= Html::a($item['name'], ['home/index', 'directory' => $relativePath]) ?>
                    </td>
                    <td></td>
                <?php else: ?>
                    <!-- 文件 -->
                    <td>
                        <?= Html::tag('i', '', ['class' => $item['type']]) ?>
                        <?= Html::a($item['name'], ['home/download', 'relativePath' => $relativePath]) ?>
                    </td>
                    <td><?= Html::a(Html::tag('i', '', ['class' => 'fa-regular fa-circle-down']), ['home/download', 'relativePath' => $relativePath]) ?></td>
                <?php endif; ?>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>