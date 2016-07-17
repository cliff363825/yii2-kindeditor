<?php
namespace cliff363825\kindeditor;

use yii\web\AssetBundle;

/**
 * Class KindEditorAsset
 * @package cliff363825\kindeditor
 */
class KindEditorAsset extends AssetBundle
{
    public $sourcePath = '@cliff363825/kindeditor/assets';
    public $js = [
        YII_ENV_DEV ? 'kindeditor-all.js' : 'kindeditor-all-min.js',
    ];
    public $css = [
        'themes/default/default.css',
    ];
}
