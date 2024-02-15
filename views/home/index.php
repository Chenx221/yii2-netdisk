<?php

/* @var $this yii\web\View */
/* @var $directoryContents array 文件和文件夹内容数组 */
/* @var $parentDirectory string 父目录 */

/* @var $directory string 当前路径 */

use app\models\NewFolderForm;
use app\models\RenameForm;
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
FontAwesomeAsset::register($this);
JqueryAsset::register($this);
$this->registerCssFile('@web/css/home_style.css');
?>
<div class="home-directory">
    <div class="d-flex justify-content-between align-items-center">
        <h1><?= Html::encode($this->title) ?></h1>
        <div>
            <?= Html::button('下载', ['class' => 'btn btn-outline-primary single-download-btn']) ?>
            <?= Html::button('下载', ['class' => 'btn btn-outline-primary batch-zip-download-btn']) ?>
            <?= Html::button('压缩', ['class' => 'btn btn-outline-primary batch-zip-btn']) ?>
            <?= Html::button('解压', ['class' => 'btn btn-outline-primary unzip-btn']) ?>
            <?= Html::button('重命名', ['class' => 'btn btn-outline-primary single-rename-btn']) ?>
            <?= Html::button('复制', ['class' => 'btn btn-outline-primary batch-copy-btn']) ?>
            <?= Html::button('剪切', ['class' => 'btn btn-outline-primary batch-cut-btn']) ?>
            <?= Html::button('粘贴', ['class' => 'btn btn-outline-primary batch-paste-btn']) ?>
            <?= Html::button('计算校验', ['class' => 'btn btn-outline-primary calc-sum-btn']) ?>
            <?= Html::button('分享', ['class' => 'btn btn-outline-primary single-share-btn']) ?>
            <?= Html::button('删除', ['class' => 'btn btn-outline-danger batch-delete-btn']) ?>
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
                    <!--                上传文件夹功能不支持IOS设备以及Firefox for Android，上传时会跳过空文件夹-->
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li><?= Html::button('离线下载', ['class' => 'dropdown-item offline-download-btn']) ?></li>
                </ul>
            </div>
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

    <nav style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='%236c757d'/%3E%3C/svg%3E&#34;);"
         aria-label="breadcrumb">
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

    <table class="table table-hover" id="drop-area">
        <thead class="table-light">
        <tr>
            <th scope="col" class="selector-col"><input type="checkbox" id="select-all"></th>
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
                <td><input type="checkbox" class="select-item"></td>
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
                        <?= Html::a($item['name'], ['home/download', 'relativePath' => $relativePath], ['class' => 'file_name']) ?>
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

$this->registerJsFile('@web/js/home_script.js', ['depends' => [JqueryAsset::class], 'position' => View::POS_END]);
?>

