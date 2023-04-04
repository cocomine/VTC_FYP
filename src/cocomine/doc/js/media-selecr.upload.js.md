media-select.upload javascript library
===
> 需要載入依賴 [media-select](media-select.js.md)

需要載入css檔案, 如已經載入則不需要
```html
<link rel="stylesheet" href="/assets/css/myself/media-select.css">
```

多語言
---
要使用多種語言，您必須具有以下html
```html
<pre id="media-select-LangJson" class="d-none">以json格式放置文本</pre>
```
```json
{
    "upload": "",
    "drag": "",
    "or": "",
    "limit_type": "",
    "limit": "",
    "respond": {
        "Over_size": "",
        "Not_complete": "",
        "Not_complete_upload": "",
        "File_type_not_mach": "",
        "File_name_over": "",
        "File_convert_fail": "",
        "File_sql_fail": "",
        "File_save_fail": "",
        "Uploaded": "",
        "Timeout": "",
        "Waiting": ""
    }
}
```
> 你可以參考第22到31行

用法
---
載入插件`media-select.upload`即可以, 需要跟`media-select`一齊載入
```javascript
//HTML script tag內
require.config({
    paths: {
        'media-select': 'myself/media-select',
        'media-select.upload': 'myself/media-select.upload',
    }
})
loadModules(['media-select', 'media-select.upload'])
```

設置接受檔案類型
---
> 作用: 設置file input element接受檔案類型

 ```javascript
function setInputAccept(mime)
```

參數:

* `string` mine 接受檔案類型, [接受參數](https://developer.mozilla.org/en-US/docs/Web/HTML/Element/input/file#accept)

例子:

```javascript
define(['jquery', 'media-select', 'media-select.upload'], function (jq, media_select, media_upload) {
    media_upload.setInputAccept("image/*, voide/*")
})
```