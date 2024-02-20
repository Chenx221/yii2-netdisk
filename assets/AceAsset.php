<?php

namespace app\assets;

use yii\web\AssetBundle;

class AceAsset extends AssetBundle
{
    public $sourcePath = '@npm/ace-builds';
    public $js = [
        'src-min/ace.js',
        'src-min/theme-github.js',
        'src-min/mode-text.js'
    ];
    public $css = [
        'css/ace.css'
    ];
}