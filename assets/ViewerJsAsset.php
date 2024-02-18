<?php

namespace app\assets;

use yii\web\AssetBundle;

class ViewerJsAsset extends AssetBundle
{
    public $sourcePath = '@npm/viewerjs/dist';
    public $js = [
        'viewer.min.js',
    ];
}