<?php

/* @var $this yii\web\View */
/* @var $directoryContents array 文件和文件夹内容数组 */
/* @var $parentDirectory string 父目录 */

/* @var $directory string 当前路径 */

use app\models\RenameForm;
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;
use app\assets\FontAwesomeAsset;
use yii\bootstrap5\Modal;
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
            <?php $absolutePath = Yii::getAlias('@app') . '/data/' . Yii::$app->user->id . '/' . $relativePath; ?>
            <tr>
                <?php if (is_dir($absolutePath)): ?>
                    <td>
                        <?= Html::tag('i', '', ['class' => $item['type'] . ' file_icon']) ?>
                        <?= Html::a($item['name'], ['home/index', 'directory' => $relativePath], ['class' => 'file_name']) ?>
                    </td>
                    <td>
                        <?= Html::button(Html::tag('i', '', ['class' => 'fa-solid fa-download']), [
                            'value' => Url::to(['home/download', 'relativePath' => $relativePath]),
                            'class' => 'btn btn-outline-primary folder-download-btn',
                            'data-bs-toggle' => 'tooltip',
                            'data-bs-placement' => 'top',
                            'data-bs-title' => '打包下载'
                        ]) ?>
                        <?= Html::button(Html::tag('i', '', ['class' => 'fa-regular fa-pen-to-square']), ['value' => $relativePath, 'class' => 'btn btn-outline-secondary rename-btn', 'data-bs-toggle' => 'tooltip', 'data-bs-placement' => 'top', 'data-bs-title' => '重命名']) ?>
                        <?= Html::button(Html::tag('i','',['class' => 'fa-regular fa-trash-can']),['value' => $relativePath,'class' =>'btn btn-outline-danger delete-btn', 'data-bs-toggle' => 'tooltip', 'data-bs-placement' => 'top', 'data-bs-title' => '删除'])?>
                    </td>
                <?php else: ?>
                    <td>
                        <?= Html::tag('i', '', ['class' => $item['type'] . ' file_icon']) ?>
                        <?= Html::a($item['name'], ['home/download', 'relativePath' => $relativePath], ['class' => 'file_name']) ?>
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
                        <?= Html::button(Html::tag('i','',['class' => 'fa-regular fa-trash-can']),['value' => $relativePath,'class' =>'btn btn-outline-danger delete-btn', 'data-bs-toggle' => 'tooltip', 'data-bs-placement' => 'top', 'data-bs-title' => '删除'])?>
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
$this->registerJsFile('@web/js/home_script.js', ['depends' => [JqueryAsset::class],'position' => View::POS_END]);
?>

