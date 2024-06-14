<?php

namespace app\assets;

use yii\web\AssetBundle;

class TSParticlesAsset extends AssetBundle
{
    //use remote js file instead of local file (for better performance?)
    public $js = [
        'https://cdn.jsdelivr.net/npm/@tsparticles/slim@3.4.0/tsparticles.slim.bundle.min.js'
    ];
}