<?php

use yii\helpers\Html;
use yii\web\JqueryAsset;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $siteConfig app\models\SiteConfig */
JqueryAsset::register($this);
$this->title = '系统设置';
?>
    <div class="admin-system">

        <h1><?= Html::encode($this->title) ?></h1>

        <br>

        <?php $form = ActiveForm::begin(); ?>

        <table>
            <?php
            $attributes = array_keys(get_object_vars($siteConfig));
            foreach ($attributes as $attribute) {
                echo '<tr id="tr-'.$attribute.'">';
                echo '<th style="width: 200px"><p>' . $siteConfig->attributeLabels()[$attribute] . '</p></th>';
                if ($attribute == 'registrationEnabled' || $attribute == 'enableIpinfo') {
                    echo '<td>' . $form->field($siteConfig, $attribute)->checkbox(['class' => 'form-check-input'], false)->label(false) . '</td>';
                } elseif ($attribute == 'verifyProvider') {
                    echo '<td>' . $form->field($siteConfig, $attribute)->dropDownList(['None' => 'None', 'reCAPTCHA' => 'reCAPTCHA', 'hCaptcha' => 'hCaptcha', 'Turnstile' => 'Turnstile'], ['class' => 'form-select form-select-sm', 'style' => 'width:25em'])->label(false) . '</td>';
                } else {
                    echo '<td>' . $form->field($siteConfig, $attribute)->textInput(['class' => 'form-control form-control-sm', 'style' => 'width:25em'])->label(false) . '</td>';
                }
                echo '</tr>';
            }
            ?>
        </table>

        <div class="form-group">
            <?= Html::submitButton('保存', ['class' => 'btn btn-success']) ?>
        </div>

        <?php ActiveForm::end(); ?>

    </div>
<?php
$this->registerJsFile('@web/js/admin-system.js', ['depends' => [JqueryAsset::class], 'position' => View::POS_END]);
?>