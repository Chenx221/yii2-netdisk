<?php

namespace app\assets;

use yii\web\AssetBundle;

class QuillAsset extends AssetBundle
{
    public $sourcePath = '@npm/quill/dist';
    public $js = [
        'quill.js',
    ];
    public $css = [
        'quill.snow.css',
        'quill.bubble.css',
    ];
}