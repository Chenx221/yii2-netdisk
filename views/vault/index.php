<?php
// 文件管理精简版是吗？

/* @var $this yii\web\View */
/* @var $directoryContents array 文件和文件夹内容数组 */
/* @var $parentDirectory string 父目录 */
/* @var $usedSpace int */
/* @var $vaultUsedSpace int */
/* @var $storageLimit int */

/* @var $directory string 当前路径 */

use app\utils\FileSizeHelper;
use yii\bootstrap5\Html;
use app\assets\FontAwesomeAsset;
use yii\bootstrap5\Modal;
use yii\bootstrap5\Progress;
use yii\helpers\Url;
use yii\web\JqueryAsset;
use yii\web\View;

$this->title = '文件保险箱';
$this->params['breadcrumbs'][] = $this->title;
$totalUsed_F = FileSizeHelper::formatBytes($usedSpace + $vaultUsedSpace); //总已用空间 格式化文本
$storageLimit_F = FileSizeHelper::formatMegaBytes($storageLimit); //存储限制 格式化文本
$is_unlimited = ($storageLimit === -1); //检查是否为无限制容量
$usedPercent = $is_unlimited ? 0 : round($usedSpace / ($storageLimit * 1024 * 1024) * 100); //网盘已用百分比
$vaultUsedPercent = $is_unlimited ? 0 : round($vaultUsedSpace / ($storageLimit * 1024 * 1024) * 100); //保险箱已用百分比
$totalUsedPercent = min(($usedPercent + $vaultUsedPercent), 100); //总已用百分比
$freeSpace = $is_unlimited ? 'unlimited' : ($storageLimit * 1024 * 1024 - $usedSpace - $vaultUsedSpace); //剩余空间
FontAwesomeAsset::register($this);
JqueryAsset::register($this);
$this->registerCssFile('@web/css/home_style.css');
?>
<div class="home-directory">
    <div class="d-flex justify-content-between align-items-center">
        <h1><?= Html::encode($this->title) ?></h1>
        <div>
            <?= Html::button('下载', ['class' => 'btn btn-outline-primary single-download-btn']) ?>
            <?= Html::button('删除', ['class' => 'btn btn-outline-danger batch-delete-btn']) ?>
            <?= Html::button('刷新', ['class' => 'btn btn-outline-primary refresh-btn']) ?>
            <div class="dropdown d-inline-block">
                <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton"
                        data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fa-solid fa-arrow-up-from-bracket"></i> 上传文件
                </button>
                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton" style="z-index: 1080;">
                    <li hidden>
                        <input type="file" id="file-input" name="uploadFile" multiple>
                        <input type="file" id="folder-input" name="uploadFile" multiple webkitdirectory>
                        <input type="hidden" name="targetDir" value="<?= $directory ?>" id="target-dir">
                    </li>
                    <li><?= Html::button('上传文件', ['class' => 'dropdown-item file-upload-btn']) ?></li>
                    <!--                上传文件功能将会覆盖已存在的同名文件，这点请注意-->
                </ul>
            </div>
            <button type="button" class="btn btn-primary" data-bs-toggle="popover" data-bs-title="容量使用情况"
                    data-bs-placement="bottom"
                    data-bs-content="已用:<?= $totalUsed_F ?>/ <?= $storageLimit_F ?><?= $freeSpace == 'unlimited' ? '' : ($freeSpace <= 0 ? ' 容量超限,功能受限' : '') ?>">
                <i
                        class="fa-solid fa-info"></i>
            </button>
        </div>
    </div>

    <!--上传进度条-->
    <?php
    echo Progress::widget([
        'percent' => 0,
        'barOptions' => ['class' => ['bg-success', 'progress-bar-animated', 'progress-bar-striped']],
        'label' => '123', //NMD 不是说可选吗
        'options' => ['style' => 'display: none;margin-top: 10px;', 'id' => 'progress-bar']
    ]);
    ?>

    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <?= Html::a('<i class="fa-solid fa-house"></i> HOME', ['vault/index'], ['class' => 'breadcrumb-item']) ?>
            <?php if ($directory !== null): ?>
                <?php
                $parts = explode('/', $directory);
                $path = '';
                $lastIndex = count($parts) - 1;
                foreach ($parts as $index => $part):
                    $path .= $part;
                    $class = $index === $lastIndex ? 'breadcrumb-item active' : 'breadcrumb-item';
                    echo Html::a($part, ['vault/index', 'directory' => $path], ['class' => $class]);
                    $path .= '/';
                endforeach;
                ?>
            <?php endif; ?>
        </ol>
    </nav>
    <div class="dropdown" id="contextMenu" style="display: none;position: absolute">
        <ul class="dropdown-menu shadow" aria-labelledby="dropdownMenuButton" id="contextMenu-content">
            <li><a class="dropdown-item" id="option-download" href="#">下载</a></li>
            <li><a class="dropdown-item" id="option-batch-delete" href="#">删除</a></li>
            <li><a class="dropdown-item" id="option-refresh" href="#">刷新</a></li>
        </ul>
    </div>
    <table class="table table-hover" id="drop-area">
        <thead>
        <tr>
            <th scope="col" class="selector-col"><label for="select-all" hidden></label><input type="checkbox"
                                                                                               id="select-all"></th>
            <th scope="col" class="name-col">名称</th>
            <th scope="col" class="modified-col">最近修改时间</th>
            <th scope="col" class="size-col">大小</th>
            <th scope="col" class="action-col">操作</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($directoryContents as $item): ?>
            <?php $relativePath = $directory ? $directory . '/' . $item['name'] : $item['name']; ?>
            <?php $absolutePath = Yii::getAlias('@app') . '/data/' . Yii::$app->user->id . '.secret/' . $relativePath; ?>
            <tr>
                <td><label for="selector1" hidden></label><input id="selector1" type="checkbox" class="select-item"
                                                                 data-relative-path="<?= Html::encode($relativePath) ?>"
                                                                 data-is-directory="<?= Html::encode(is_dir($absolutePath)) ?>">
                </td>
                <td>
                    <?= Html::tag('i', '', ['class' => $item['type'] . ' file_icon']) ?>
                    <?= Html::a($item['name'], ['vault/download', 'relativePath' => $relativePath], ['class' => 'file_name']) ?>
                </td>
                <td class="file_info">
                    <?= date('Y-m-d H:i:s', $item['lastModified']) ?>
                </td>
                <td class="file_info">
                    <?= $item['size'] !== null ? Yii::$app->formatter->asShortSize($item['size'], 2) : '' ?>
                </td>
                <td>
                    <?= Html::button(Html::tag('i', '', ['class' => 'fa-regular fa-circle-down']), [
                        'value' => Url::to(['vault/download', 'relativePath' => $relativePath]),
                        'class' => 'btn btn-outline-primary download-btn',
                        'data-bs-toggle' => 'tooltip',
                        'data-bs-placement' => 'top',
                        'data-bs-title' => '下载'
                    ]) ?>
                    <?= Html::button(Html::tag('i', '', ['class' => 'fa-regular fa-trash-can']), ['value' => $relativePath, 'class' => 'btn btn-outline-danger delete-btn', 'data-bs-toggle' => 'tooltip', 'data-bs-placement' => 'top', 'data-bs-title' => '删除']) ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php
Modal::begin([
    'title' => '<h4>确认删除</h4>',
    'id' => 'deleteModal',
    'size' => 'modal-sm',
]);

echo Html::tag('div', '你确定要删除这个文件吗？', ['class' => 'modal-body']);

echo Html::beginForm(['vault/delete'], 'post', ['id' => 'delete-form']);
echo Html::hiddenInput('relativePath', '', ['id' => 'deleteRelativePath']);
echo Html::submitButton('确认', ['class' => 'btn btn-danger']);
echo Html::endForm();

Modal::end();
$this->registerJsFile('@web/js/vault_script.js', ['depends' => [JqueryAsset::class], 'position' => View::POS_END]);
?>

