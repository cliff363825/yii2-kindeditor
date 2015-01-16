<?php

namespace cliff363825\kindeditor;

use Yii;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\web\JsExpression;
use yii\widgets\InputWidget;

class KindEditorWidget extends InputWidget
{
    const PLUGIN_NAME = 'KindEditor';

    /**
     * KindEditor Options
     * @var array
     */
    public $clientOptions = [];

    /**
     * csrf cookie param
     * @var string
     */
    public $csrfCookieParam = '_csrfCookie';

    /**
     * @var boolean
     */
    public $render = true;

    /**
     * @inheritdoc
     */
    public function run()
    {
        if ($this->render) {
            if ($this->hasModel()) {
                echo Html::activeTextarea($this->model, $this->attribute, $this->options);
            } else {
                echo Html::textarea($this->name, $this->value, $this->options);
            }
        }
        $this->registerClientScript();
    }

    /**
     * register client scripts(css, javascript)
     */
    public function registerClientScript()
    {
        $view = $this->getView();
        $this->initClientOptions();
        $asset = KindEditorAsset::register($view);
        $themeType = isset($this->clientOptions['themeType']) ? $this->clientOptions['themeType'] : 'default';
        if ($themeType != 'default') {
            $view->registerCssFile($asset->baseUrl . "/themes/{$themeType}/{$themeType}.css", ['depends' => '\cliff363825\kindeditor\KindEditorAsset']);
        }
        $preJs = '';
        if ($themeType == 'qq') {
            $this->clientOptions['items'] = [
                'bold', 'italic', 'underline', 'fontname', 'fontsize', 'forecolor', 'hilitecolor', 'plug-align', 'plug-order', 'plug-indent', 'link',
            ];
            $preJs = "
K.each({
    'plug-align' : {
        name : '对齐方式',
        method : {
            'justifyleft' : '左对齐',
            'justifycenter' : '居中对齐',
            'justifyright' : '右对齐'
        }
    },
    'plug-order' : {
        name : '编号',
        method : {
            'insertorderedlist' : '数字编号',
            'insertunorderedlist' : '项目编号'
        }
    },
    'plug-indent' : {
        name : '缩进',
        method : {
            'indent' : '向右缩进',
            'outdent' : '向左缩进'
        }
    }
},function( pluginName, pluginData ){
    var lang = {};
    lang[pluginName] = pluginData.name;
    KindEditor.lang( lang );
    KindEditor.plugin( pluginName, function(K) {
        var self = this;
        self.clickToolbar( pluginName, function() {
            var menu = self.createMenu({
                    name : pluginName,
                    width : pluginData.width || 100
                });
            K.each( pluginData.method, function( i, v ){
                menu.addItem({
                    title : v,
                    checked : false,
                    iconClass : pluginName+'-'+i,
                    click : function() {
                        self.exec(i).hideMenu();
                    }
                });
            })
        });
    });
});
";
        } elseif ($themeType == 'simple') {
            $this->clientOptions['items'] = [
                'fontname', 'fontsize', '|',
                'forecolor', 'hilitecolor', 'bold', 'italic', 'underline', 'removeformat', '|',
                'justifyleft', 'justifycenter', 'justifyright', 'insertorderedlist', 'insertunorderedlist', '|',
                'emoticons', 'image', 'link',
            ];
        }
        $view->registerJsFile($asset->baseUrl . '/lang/' . $this->clientOptions['langType'] . '.js', ['depends' => '\cliff363825\kindeditor\KindEditorAsset']);
        $id = $this->options['id'];
        $varName = self::PLUGIN_NAME . '_' . str_replace('-', '_', $id);
        $js = "
KindEditor.ready(function(K) {
    {$preJs};
    var {$varName} = K.create('#{$id}'," . Json::encode($this->clientOptions) . ");});
";
        $view->registerJs($js);
    }

    /**
     * client options init
     */
    protected function initClientOptions()
    {
        // KindEditor optional parameters
        $params = [
            'width',
            'height',
            'minWidth',
            'minHeight',
            'items',
            'noDisableItems',
            'filterMode',
            'htmlTags',
            'wellFormatMode',
            'resizeType',
            'themeType',
            'langType',
            'designMode',
            'fullscreenMode',
            'basePath',
            'themesPath',
            'pluginsPath',
            'langPath',
            'minChangeSize',
            'urlType',
            'newlineTag',
            'pasteType',
            'dialogAlignType',
            'shadowMode',
            'zIndex',
            'useContextmenu',
            'syncType',
            'indentChar',
            'cssPath',
            'cssData',
            'bodyClass',
            'colorTable',
            'afterCreate',
            'afterChange',
            'afterTab',
            'afterFocus',
            'afterBlur',
            'afterUpload',
            'uploadJson',
            'fileManagerJson',
            'allowPreviewEmoticons',
            'allowImageUpload',
            'allowFlashUpload',
            'allowMediaUpload',
            'allowFileUpload',
            'allowFileManager',
            'fontSizeTable',
            'imageTabIndex',
            'formatUploadUrl',
            'fullscreenShortcut',
            'extraFileUploadParams',
            'filePostName',
            'fillDescAfterUploadImage',
            'afterSelectFile',
            'pagebreakHtml',
            'allowImageRemote',
            'autoHeightMode',
        ];
        $options = [];
        $options['width'] = '680px';
        $options['height'] = '350px';
        $options['themeType'] = 'default';
        $options['langType'] = 'zh_CN';
        $options['afterChange'] = new JsExpression('function(){this.sync();}');
        foreach ($params as $key) {
            if (isset($this->clientOptions[$key])) {
                $options[$key] = $this->clientOptions[$key];
            }
        }
        // $_POST['_csrf'] = ...
        $options['extraFileUploadParams'][Yii::$app->request->csrfParam] = Yii::$app->request->getCsrfToken();
        // $_POST['PHPSESSID'] = ...
        $options['extraFileUploadParams'][Yii::$app->session->name] = Yii::$app->session->id;
        if (Yii::$app->request->enableCsrfCookie) {
            // $_POST['_csrfCookie'] = ...
            $options['extraFileUploadParams'][$this->csrfCookieParam] = $_COOKIE[Yii::$app->request->csrfParam];
        }
        $this->clientOptions = $options;
    }
}
