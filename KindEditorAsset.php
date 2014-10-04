<?php

namespace cliff363825\kindeditor;

use yii\web\AssetBundle;

class KindEditorAsset extends AssetBundle
{
    public $sourcePath = '@cliff363825/kindeditor/assets';
    public $js = [
        'kindeditor.js',
    ];
    public $css = [
        'themes/default/default.css',
    ];

}
