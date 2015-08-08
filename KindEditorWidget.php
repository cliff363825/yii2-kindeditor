<?php
namespace cliff363825\kindeditor;

use Yii;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\web\JsExpression;
use yii\widgets\InputWidget;

/**
 * Class KindEditorWidget
 * @package cliff363825\kindeditor
 */
class KindEditorWidget extends InputWidget
{
    /**
     * The name of this widget.
     */
    const PLUGIN_NAME = 'KindEditor';

    const THEME_TYPE_DEFAULT = 'default';
    const THEME_TYPE_QQ = 'qq';
    const THEME_TYPE_SIMPLE = 'simple';

    const LANG_TYPE_AR = 'ar';
    const LANG_TYPE_EN = 'en';
    const LANG_TYPE_KO = 'ko';
    const LANG_TYPE_ZH_CN = 'zh_CN';
    const LANG_TYPE_ZH_TW = 'zh_TW';

    /**
     * @var array the KindEditor plugin options.
     * @see http://kindeditor.net/doc.php
     */
    public $clientOptions = [];

    /**
     * @inheritdoc
     */
    public function run()
    {
        $this->registerClientScript();
        if ($this->hasModel()) {
            echo Html::activeTextarea($this->model, $this->attribute, $this->options);
        } else {
            echo Html::textarea($this->name, $this->value, $this->options);
        }
    }

    /**
     * Registers the needed client script and options.
     */
    public function registerClientScript()
    {
        $view = $this->getView();
        $this->initClientOptions();
        $asset = KindEditorAsset::register($view);
        $themeType = !empty($this->clientOptions['themeType']) ? $this->clientOptions['themeType'] : self::THEME_TYPE_DEFAULT;
        if ($themeType != self::THEME_TYPE_DEFAULT) {
            $view->registerCssFile($asset->baseUrl . "/themes/{$themeType}/{$themeType}.css", ['depends' => '\cliff363825\kindeditor\KindEditorAsset']);
        }
        $preJs = '';
        if ($themeType == self::THEME_TYPE_QQ) {
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
        } elseif ($themeType == self::THEME_TYPE_SIMPLE) {
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
     * Initializes client options
     */
    protected function initClientOptions()
    {
        $options = array_merge($this->defaultOptions(), $this->clientOptions);
        // $_POST['_csrf'] = ...
        $options['extraFileUploadParams'][Yii::$app->request->csrfParam] = Yii::$app->request->getCsrfToken();
        // $_POST['PHPSESSID'] = ...
        $options['extraFileUploadParams'][Yii::$app->session->name] = Yii::$app->session->id;
        $this->clientOptions = $options;
    }

    /**
     * Default client options
     * @return array
     */
    protected function defaultOptions()
    {
        return [
            'width' => '680px',
            'height' => '350px',
            'themeType' => self::THEME_TYPE_DEFAULT,
            'langType' => self::LANG_TYPE_ZH_CN,
            'afterChange' => new JsExpression('function(){this.sync();}'),
        ];
    }
}
