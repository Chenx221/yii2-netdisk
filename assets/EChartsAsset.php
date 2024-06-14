<?php

namespace app\assets;

use yii\web\AssetBundle;

class EChartsAsset extends AssetBundle
{
    public $sourcePath = '@npm/echarts/dist';
    public $js = [
        'echarts.min.js',
    ];
}