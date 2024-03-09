<?php

/* @var $this yii\web\View */
/* @var $directoryContents array 文件和文件夹内容数组 */
/* @var $parentDirectory string 父目录 */
/* @var $usedSpace int */
/* @var $vaultUsedSpace int */
/* @var $storageLimit int */

/* @var $directory string 当前路径 */

use app\assets\AceAsset;
use app\assets\PlyrAsset;
use app\assets\ViewerJsAsset;
use app\models\CollectionTasks;
use app\models\NewFolderForm;
use app\models\RenameForm;
use app\models\Share;
use app\models\ZipForm;
use app\utils\FileSizeHelper;
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;
use app\assets\FontAwesomeAsset;
use yii\bootstrap5\Modal;
use yii\bootstrap5\Progress;
use yii\helpers\Url;
use yii\web\JqueryAsset;
use yii\web\View;

$this->title = '文件管理';
$this->params['breadcrumbs'][] = $this->title;
$totalUsed_F = FileSizeHelper::formatBytes($usedSpace + $vaultUsedSpace); //总已用空间 格式化文本
$storageLimit_F = FileSizeHelper::formatMegaBytes($storageLimit); //存储限制 格式化文本
$is_unlimited = ($storageLimit === -1); //检查是否为无限制容量
$usedPercent = $is_unlimited ? 0 : round($usedSpace / ($storageLimit * 1024 * 1024) * 100); //网盘已用百分比
$vaultUsedPercent = $is_unlimited ? 0 : round($vaultUsedSpace / ($storageLimit * 1024 * 1024) * 100); //保险箱已用百分比
$totalUsedPercent = min(($usedPercent + $vaultUsedPercent), 100); //总已用百分比

FontAwesomeAsset::register($this);
JqueryAsset::register($this);
ViewerJsAsset::register($this);
PlyrAsset::register($this);
AceAsset::register($this);
$this->registerCssFile('@web/css/home_style.css');
?>
<div class="home-directory">
    <div class="d-flex justify-content-between align-items-center">
        <h1><?= Html::encode($this->title) ?></h1>
        <div>
            <?= Html::button('打开', ['class' => 'btn btn-outline-primary single-open-btn']) ?>
            <?= Html::button('下载', ['class' => 'btn btn-outline-primary single-download-btn']) ?>
            <?= Html::button('打包下载', ['class' => 'btn btn-outline-primary batch-zip-download-btn']) ?>
            <?= Html::button('压缩', ['class' => 'btn btn-outline-primary batch-zip-btn']) ?>
            <?= Html::button('解压', ['class' => 'btn btn-outline-primary unzip-btn']) ?>
            <?= Html::button('重命名', ['class' => 'btn btn-outline-primary single-rename-btn']) ?>
            <?= Html::button('复制', ['class' => 'btn btn-outline-primary batch-copy-btn']) ?>
            <?= Html::button('剪切', ['class' => 'btn btn-outline-primary batch-cut-btn']) ?>
            <?= Html::button('粘贴', ['class' => 'btn btn-outline-primary batch-paste-btn']) ?>
            <?= Html::button('计算校验', ['class' => 'btn btn-outline-primary calc-sum-btn']) ?>
            <?= Html::button('分享', ['class' => 'btn btn-outline-primary single-share-btn']) ?>
            <?= Html::button('删除', ['class' => 'btn btn-outline-danger batch-delete-btn']) ?>
            <?= Html::button('收集文件', ['class' => 'btn btn-outline-primary create-collection-btn']) ?>
            <?= Html::button('刷新', ['class' => 'btn btn-outline-primary refresh-btn']) ?>
            <?= Html::button('新建文件夹', ['class' => 'btn btn-outline-primary new-folder-btn', 'value' => $directory]) ?>
            <div class="dropdown d-inline-block">
                <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton"
                        data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fa-solid fa-arrow-up-from-bracket"></i> 上传文件
                </button>
                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                    <li hidden>
                        <input type="file" id="file-input" name="uploadFile" multiple>
                        <input type="file" id="folder-input" name="uploadFile" multiple webkitdirectory>
                        <input type="hidden" name="targetDir" value="<?= $directory ?>" id="target-dir">
                    </li>
                    <li><?= Html::button('上传文件', ['class' => 'dropdown-item file-upload-btn']) ?></li>
                    <!--                上传文件功能将会覆盖已存在的同名文件，这点请注意-->
                    <li><?= Html::button('上传文件夹', ['class' => 'dropdown-item folder-upload-btn']) ?></li>
                </ul>
            </div>
            <button type="button" class="btn btn-primary" data-bs-toggle="popover" data-bs-title="容量使用情况"
                    data-bs-placement="bottom" data-bs-content="已用:<?= $totalUsed_F ?>/ <?= $storageLimit_F ?>"><i
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
            <?= Html::a('<i class="fa-solid fa-house"></i> HOME', ['home/index'], ['class' => 'breadcrumb-item']) ?>
            <?php if ($directory !== null): ?>
                <?php
                $parts = explode('/', $directory);
                $path = '';
                $lastIndex = count($parts) - 1;
                foreach ($parts as $index => $part):
                    $path .= $part;
                    $class = $index === $lastIndex ? 'breadcrumb-item active' : 'breadcrumb-item';
                    echo Html::a($part, ['home/index', 'directory' => $path], ['class' => $class]);
                    $path .= '/';
                endforeach;
                ?>
            <?php endif; ?>
        </ol>
    </nav>
    <div class="dropdown" id="contextMenu" style="display: none;position: absolute">
        <ul class="dropdown-menu shadow" aria-labelledby="dropdownMenuButton" id="contextMenu-content">
            <li><a class="dropdown-item" id="option-open" href="#">打开</a></li>
            <li><a class="dropdown-item" id="option-download" href="#">下载</a></li>
            <li><a class="dropdown-item" id="option-batch-zip-download" href="#">打包下载</a></li>
            <li><a class="dropdown-item" id="option-single-rename" href="#">重命名</a></li>
            <li><a class="dropdown-item" id="option-batch-copy" href="#">复制</a></li>
            <li><a class="dropdown-item" id="option-batch-cut" href="#">剪切</a></li>
            <li><a class="dropdown-item" id="option-batch-paste" href="#">粘贴</a></li>
            <li><a class="dropdown-item" id="option-batch-delete" href="#">删除</a></li>
            <li><a class="dropdown-item" id="option-refresh" href="#">刷新</a></li>
        </ul>
    </div>
    <img id="hidden-image" style="display: none;" alt="" src="" loading=lazy>
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
            <?php $absolutePath = Yii::getAlias('@app') . '/data/' . Yii::$app->user->id . '/' . $relativePath; ?>
            <tr>
                <td><label for="selector1" hidden></label><input id="selector1" type="checkbox" class="select-item"
                                                                 data-relative-path="<?= Html::encode($relativePath) ?>"
                                                                 data-is-directory="<?= Html::encode(is_dir($absolutePath)) ?>">
                </td>
                <?php if (is_dir($absolutePath)): ?> <!-- 如果是文件夹 -->
                    <td>
                        <?= Html::tag('i', '', ['class' => $item['type'] . ' file_icon']) ?>
                        <?= Html::a($item['name'], ['home/index', 'directory' => $relativePath], ['class' => 'file_name']) ?>
                    </td>
                    <td class="file_info">
                        <?= date('Y-m-d H:i:s', $item['lastModified']) ?>
                    </td>
                    <td class="file_info">
                        ---
                    </td>
                    <td>
                        <?= Html::button(Html::tag('i', '', ['class' => 'fa-solid fa-download']), [
                            'value' => $relativePath,
                            'class' => 'btn btn-outline-primary folder-download-btn',
                            'data-bs-toggle' => 'tooltip',
                            'data-bs-placement' => 'top',
                            'data-bs-title' => '打包下载'
                        ]) ?>
                        <?= Html::button(Html::tag('i', '', ['class' => 'fa-regular fa-pen-to-square']), ['value' => $relativePath, 'class' => 'btn btn-outline-secondary rename-btn', 'data-bs-toggle' => 'tooltip', 'data-bs-placement' => 'top', 'data-bs-title' => '重命名']) ?>
                        <?= Html::button(Html::tag('i', '', ['class' => 'fa-solid fa-share-nodes']), ['value' => $relativePath, 'class' => 'btn btn-outline-success shares-btn', 'data-bs-toggle' => 'tooltip', 'data-bs-placement' => 'top', 'data-bs-title' => '分享']) ?>
                        <?= Html::button(Html::tag('i', '', ['class' => 'fa-regular fa-trash-can']), ['value' => $relativePath, 'class' => 'btn btn-outline-danger delete-btn', 'data-bs-toggle' => 'tooltip', 'data-bs-placement' => 'top', 'data-bs-title' => '删除']) ?>
                    </td>
                <?php else: ?> <!-- 如果是文件 -->
                    <td>
                        <?= Html::tag('i', '', ['class' => $item['type'] . ' file_icon']) ?>
                        <?php if ($item['type'] === 'fa-regular fa-file-image'): ?>
                            <?= Html::a($item['name'], ['home/preview', 'relativePath' => $relativePath], ['class' => 'file_name', 'onclick' => 'previewImage(this, event)']) ?>
                        <?php elseif ($item['type'] === 'fa-regular fa-file-video'): ?>
                            <?= Html::a($item['name'], ['home/download', 'relativePath' => $relativePath, 'type' => $item['rawType']], ['class' => 'file_name', 'onclick' => 'previewVideo(this, event)']) ?>
                        <?php elseif ($item['type'] === 'fa-regular fa-file-lines' || $item['type'] === 'fa-regular fa-file-code' || $item['type'] === 'fa-solid fa-gears'): ?>
                            <?= Html::a($item['name'], ['home/download', 'relativePath' => $relativePath, 'type' => $item['rawType']], ['class' => 'file_name', 'onclick' => 'textEdit(this, event)']) ?>
                        <?php elseif ($item['type'] === 'fa-regular fa-file-audio'): ?>
                            <?= Html::a($item['name'], ['home/download', 'relativePath' => $relativePath, 'type' => $item['rawType']], ['class' => 'file_name', 'onclick' => 'previewAudio(this, event)']) ?>
                        <?php elseif ($item['type'] === 'fa-regular fa-file-pdf'): ?>
                            <?= Html::a($item['name'], ['home/preview', 'relativePath' => $relativePath, 'type' => $item['rawType']], ['class' => 'file_name', 'onclick' => 'previewPdf(this, event)']) ?>
                        <?php else: ?>
                            <?= Html::a($item['name'], ['home/download', 'relativePath' => $relativePath], ['class' => 'file_name']) ?>
                        <?php endif; ?>
                    </td>
                    <td class="file_info">
                        <?= date('Y-m-d H:i:s', $item['lastModified']) ?>
                    </td>
                    <td class="file_info">
                        <?= $item['size'] !== null ? Yii::$app->formatter->asShortSize($item['size'], 2) : '' ?>
                    </td>
                    <td>
                        <?= Html::button(Html::tag('i', '', ['class' => 'fa-regular fa-circle-down']), [
                            'value' => Url::to(['home/download', 'relativePath' => $relativePath]),
                            'class' => 'btn btn-outline-primary download-btn',
                            'data-bs-toggle' => 'tooltip',
                            'data-bs-placement' => 'top',
                            'data-bs-title' => '下载'
                        ]) ?>
                        <?= Html::button(Html::tag('i', '', ['class' => 'fa-regular fa-pen-to-square']), ['value' => $relativePath, 'class' => 'btn btn-outline-secondary rename-btn', 'data-bs-toggle' => 'tooltip', 'data-bs-placement' => 'top', 'data-bs-title' => '重命名']) ?>
                        <?= Html::button(Html::tag('i', '', ['class' => 'fa-solid fa-share-nodes']), ['value' => $relativePath, 'class' => 'btn btn-outline-success shares-btn', 'data-bs-toggle' => 'tooltip', 'data-bs-placement' => 'top', 'data-bs-title' => '分享']) ?>
                        <?= Html::button(Html::tag('i', '', ['class' => 'fa-regular fa-trash-can']), ['value' => $relativePath, 'class' => 'btn btn-outline-danger delete-btn', 'data-bs-toggle' => 'tooltip', 'data-bs-placement' => 'top', 'data-bs-title' => '删除']) ?>
                    </td>
                <?php endif; ?>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php
Modal::begin([
    'title' => '<h4>重命名文件/文件夹</h4>',
    'id' => 'renameModal',
    'size' => 'modal-lg',
]);

$model = new RenameForm();
$form = ActiveForm::begin(['id' => 'rename-form', 'action' => ['home/rename'], 'method' => 'post']);

echo $form->field($model, 'newName')->textInput(['maxlength' => true])->label('新名称');
echo Html::hiddenInput('relativePath', '', ['id' => 'renameRelativePath']);
echo Html::submitButton('提交', ['class' => 'btn btn-primary']);

ActiveForm::end();
Modal::end();

Modal::begin([
    'title' => '<h4>确认删除</h4>',
    'id' => 'deleteModal',
    'size' => 'modal-sm',
]);

echo Html::tag('div', '你确定要删除这个文件吗？', ['class' => 'modal-body']);

echo Html::beginForm(['home/delete'], 'post', ['id' => 'delete-form']);
echo Html::hiddenInput('relativePath', '', ['id' => 'deleteRelativePath']);
echo Html::submitButton('确认', ['class' => 'btn btn-danger']);
echo Html::endForm();

Modal::end();

Modal::begin([
    'title' => '<h4>新建文件夹</h4>',
    'id' => 'newFolderModal',
    'size' => 'modal-lg',
]);

$model1 = new NewFolderForm();
$form = ActiveForm::begin(['id' => 'new-folder-form', 'action' => ['home/new-folder'], 'method' => 'post', 'enableAjaxValidation' => true]);

echo $form->field($model1, 'folderName')->textInput(['maxlength' => true])->label('文件夹名称');
echo Html::hiddenInput('relativePath', '', ['id' => 'newDirRelativePath']);
echo Html::submitButton('提交', ['class' => 'btn btn-primary']);

ActiveForm::end();
Modal::end();

Modal::begin([
    'title' => '<h4>创建压缩文件</h4>',
    'id' => 'zipModal',
    'size' => 'modal-lg',
]);
$model2 = new ZipForm();
$form = ActiveForm::begin(['id' => 'zip-form', 'action' => ['home/zip'], 'method' => 'post']);

echo $form->field($model2, 'zipFilename')->textInput(['maxlength' => true])->label('压缩文件名');
echo $form->field($model2, 'zipFormat')->dropDownList(['zip' => 'ZIP', '7z' => '7Z'])->label('压缩格式');
echo Html::hiddenInput('relativePath', '', ['id' => 'zipRelativePath']);
echo Html::hiddenInput('targetDirectory', $directory, ['id' => 'zipTargetDirectory']);  // 添加这一行
echo Html::submitButton('创建', ['class' => 'btn btn-primary']);

ActiveForm::end();
Modal::end();

Modal::begin([
    'id' => 'checksumModal',
    'title' => '文件校验信息',
    'size' => 'modal-lg',
]);
echo Html::tag('p', '', ['id' => 'crc32b']);
echo Html::tag('p', '', ['id' => 'sha256']);
Modal::end();

Modal::begin([
    'title' => '<h4>创建分享</h4>',
    'id' => 'shareModal',
//    'size' => 'modal-sm',
]);
$form = ActiveForm::begin(['id' => 'share-form', 'action' => ['share/create'], 'method' => 'post']);
$model3 = new Share();
echo $form->field($model3, 'file_relative_path')->textInput(['readonly' => true])->label('文件位置');
echo $form->field($model3, 'access_code')->textInput(['maxlength' => 4])->label('访问密码(4位英文数字组合,不区分大小写)');
echo Html::button('生成密码', ['id' => 'generate_access_code', 'class' => 'btn btn-primary']);
echo str_repeat('&nbsp;', 5);  // 添加5个空格
echo Html::submitButton('提交', ['class' => 'btn btn-primary']);

ActiveForm::end();
Modal::end();

Modal::begin([
    'title' => '<h4>视频播放</h4>',
    'id' => 'videoModal',
    'size' => 'modal-xl',
]);

echo '<video id="vPlayer" controls crossorigin playsinline>
    <source src="" type="">
</video>';

Modal::end();

Modal::begin([
    'title' => '<h4>音频播放</h4>',
    'id' => 'audioModal',
]);

echo '<audio id="aPlayer" controls>
  <source src="" type="">
</audio>';

Modal::end();

Modal::begin([
    'title' => '<h4>文本编辑</h4>',
    'id' => 'textEditModal',
    'size' => 'modal-lg',
]);

echo '<div class="alert alert-success" role="alert" id="ed-alert-success">保存成功</div><div class="alert alert-danger" role="alert" id="ed-alert-fail">保存失败</div>';
echo '<button id="saveButton" class="btn btn-outline-primary" style="margin-bottom: 16px"><i class="fa-regular fa-floppy-disk"></i> 保存文件</button>';
echo '<input type="hidden" id="edFilename" value="">';
echo '<div id="editor" style="width: 100%; height: 500px;"></div>';

Modal::end();

Modal::begin([
    'title' => '<h4>PDF预览</h4>',
    'id' => 'pdfModal',
    'size' => 'modal-xl',
]);
Modal::end();

Modal::begin([
    'title' => '<h4>创建文件收集</h4>',
    'id' => 'collectionModal',
]);
$collectionTasks = new CollectionTasks();
$collectionTasks->scenario = 'create';
echo $this->render('../collection/create', [
    'model' => $collectionTasks,
]);
Modal::end();
$this->registerJsFile('@web/js/home_script.js', ['depends' => [JqueryAsset::class], 'position' => View::POS_END]);
?>

