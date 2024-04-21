<?php

namespace app\assets;

use yii\web\AssetBundle;

class TSParticlesAsset extends AssetBundle
{
    public $sourcePath = '@npm/tsparticles--slim';
    public $js = [
        'tsparticles.slim.bundle.min.js',
    ];
}