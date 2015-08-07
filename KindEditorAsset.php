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
    public $js = [];
    public $css = [
        'themes/default/default.css',
    ];

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if (YII_DEBUG) {
            $this->js[] = 'kindeditor.js';
        } else {
            $this->js[] = 'kindeditor-min.js';
        }
    }
}
