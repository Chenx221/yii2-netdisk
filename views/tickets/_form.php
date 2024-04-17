<?php

use app\assets\QuillAsset;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Tickets $model */
/** @var yii\widgets\ActiveForm $form */
QuillAsset::register($this);
?>

<div class="tickets-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'description')->hiddenInput()->label(false) ?>

    <label class="control-label" for="editor">工单内容</label>

    <div id="editor" class="form-control">

    </div>

    <br>

    <div class="form-group">
        <?= Html::submitButton('创建', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
<?php
$js = <<<JS
    var theme = document.documentElement.getAttribute('data-bs-theme')==='dark'?'bubble':'snow'
    const quill = new Quill('#editor', {
        theme: theme
    });
    $('form').on('beforeSubmit', function() {
        var length = quill.getLength();
        if (length <= 1) {
            alert('工单内容不能为空');
            return false;
        }
        var delta = quill.getContents();
        var deltaJson = JSON.stringify(delta);
        $('#tickets-description').val(deltaJson);
    });
JS;

$this->registerJs($js);
?>