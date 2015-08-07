<?php
namespace cliff363825\kindeditor;

use Yii;
use yii\web\Controller;

/**
 * Class KindEditorController
 * @package cliff363825\kindeditor
 */
class KindEditorController extends Controller
{
    /**
     * @inheritDoc
     */
    public function actions()
    {
        return [
            'upload' => [
                'class' => 'cliff363825\kindeditor\KindEditorUploadAction',
                'savePath' => '@webroot/uploads',
                'saveUrl' => '@web/uploads',
                'maxSize' => 2097152,
            ],
            'filemanager' => [
                'class' => 'cliff363825\kindeditor\KindEditorFileManagerAction',
                'rootPath' => '@webroot/uploads',
                'rootUrl' => '@web/uploads',
            ],
        ];
    }
}
