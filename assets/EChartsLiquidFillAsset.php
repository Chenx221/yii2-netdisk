<?php

namespace app\assets;

use yii\web\AssetBundle;

class EChartsLiquidFillAsset extends AssetBundle
{
    public $sourcePath = '@npm/echarts-liquidfill/dist';
    public $js = [
        'echarts-liquidfill.js',
    ];
}