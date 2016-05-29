KindEditor Widget for Yii2
==========================
**一言不合就飙洋文系列**

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

1) Without any model:

```php
<?= \cliff363825\kindeditor\KindEditorWidget::widget([
    'name' => 'content',
    'options' => [], // html attributes
    'clientOptions' => [
        'width' => '680px',
        'height' => '350px',
        'themeType' => 'default', // optional: default, simple, qq
        'langType' => \cliff363825\kindeditor\KindEditorWidget::LANG_TYPE_ZH_CN, // optional: ar, en, ko, ru, zh-CN, zh-TW
        ...
    ],
]); ?>
```

2) With an model:

```php
<?= \cliff363825\kindeditor\KindEditorWidget::widget([
    'model' => $model,
    'attribute' => 'content',
    'options' => [], // html attributes
    'clientOptions' => [
        'width' => '680px',
        'height' => '350px',
        'themeType' => 'default', // optional: default, simple, qq
        'langType' => \cliff363825\kindeditor\KindEditorWidget::LANG_TYPE_ZH_CN, // optional: ar, en, ko, ru, zh-CN, zh-TW
        ...
    ],
]); ?>
```

Notice
------
- **In version v1.0.5 or later, the language packages was renamed. zh_CN -> zh-CN, zh_TW -> zh-TW.**

>You may have to modify your code in your project if `KindEditorWidget->clientOptions->langType` was set `zh_CN` or `zh_TW`.

>说简单点，就是你要把langType是`zh_CN`、`zh_TW`对应改成`zh-CN`、`zh-TW`。

- **The default value of `KindEditorUploadAction->savePath` is changed to `uploads` now, NOT `@webroot/uploads`.**

>Add a new property `basePath` and the default value is `@webroot`.

- **Remove property `KindEditorUploadAction->saveUrl`.**

>Add a new property `baseUrl` and the default value is `@web`.

see the [change log](https://github.com/cliff363825/yii2-kindeditor/blob/master/CHANGELOG.md)

Documentation
-------------
For full details on usage, see the [documentation](http://kindeditor.net/doc.php).