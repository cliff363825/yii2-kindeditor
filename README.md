KindEditor Widget for Yii2
==========================
KindEditor Widget for Yii2

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist cliff363825/yii2-kindeditor "*"
```

or add

```
"cliff363825/yii2-kindeditor": "*"
```

to the require section of your `composer.json` file.


Usage
-----

Once the extension is installed, simply use it in your code by  :

```php
<?= \cliff363825\kindeditor\KindEditorWidget::widget([
    'name' => 'content',
    'options' => [], // html attributes
    'clientOptions' => [
        'width' => '680px',
        'height' => '350px',
        'themeType' => 'default', // optional: default, simple, qq
        'langType' => 'zh_CN', // optional: ar, en, ko, zh_CN, zh_TW
        ...
    ],
]); ?>
```

or use with a model:

```php
<?= \cliff363825\kindeditor\KindEditorWidget::widget([
    'model' => $model,
    'attribute' => 'content',
    'options' => [], // html attributes
    'clientOptions' => [
        'width' => '680px',
        'height' => '350px',
        'themeType' => 'default', // optional: default, simple, qq
        'langType' => 'zh_CN', // optional: ar, en, ko, zh_CN, zh_TW
        ...
    ],
]); ?>
```

For full details on usage, see the [documentation](http://kindeditor.net/doc.php).