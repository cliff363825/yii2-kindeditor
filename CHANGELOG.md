Yii2 KindEditor Change Log
==========================
**一言不合就飙洋文系列**


v1.0.5 under development
-----------------------

- Fixed `mkdir()` mode 0755.
- Remove unnecessary code.
- Update `KindEditor` version from 4.1.10 to 4.1.11.see the [change log](http://kindeditor.net/docs/changelog.html).
- **Waring: Language packages standardization. zh_CN -> zh-CN, zh_TW -> zh-TW. 改善: 语言包文件名标准化，zh_CN -> zh-CN, zh_TW -> zh-TW。**
- For the future, please use `KindEditorWidget::LANG_TYPE_ZH_CN` or `LANG_TYPE_ZH_TW` to avoid this problem.


v1.0.4
-----------------------

- Add comments.
- Split `KindEditorBaseController` to actions `KindEditorUploadAction`,`KindEditorFileManagerAction`
- `KindEditorBaseController` is deprecated and will be removed in the future.


1.0.3
-----------------------

- The name of `KindEditorBaseController` is too long, use `KindEditorController` instead.


1.0.2
-----------------------

- Bug #1: Fixed 400 bad request


1.0.1
-----------------------

- Update README.md


1.0.0
-----------------------

- Project init.