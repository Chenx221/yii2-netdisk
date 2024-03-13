<?php

namespace app\assets;

use yii\web\AssetBundle;

class SimpleWebAuthnBrowser extends AssetBundle
{
    public $sourcePath = '@npm/simplewebauthn--browser/dist/bundle';
    public $js = [
        'index.js',
    ];
}