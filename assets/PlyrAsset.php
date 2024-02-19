<?php

namespace app\assets;

use yii\web\AssetBundle;

class PlyrAsset extends AssetBundle
{
    public $sourcePath = '@npm/plyr/dist';
    public $js = [
        'plyr.min.js',
    ];
    public $css = [
        'plyr.css'
    ];
}