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
    "No_media": "",
    "Unknown_Error": "",
    "title": "",
    "Select": ["", ""],
    "drag": "",
    "Media": "",
    "upload": {
        "Timeout": "",
        "File_name_over": "",
        "Over_size": "",
        "File_type_not_mach": "",
        "Waiting": "",
        "limit_type": "",
        "drag": ""
    },
}
```
> 你可以參考第22到31行

用法
---
載入插件`media-select.upload`即可以, 需要跟`media-select`一齊載入
```javascript
//HTML script tag內
require.config({
    paths:{
        'media-select': 'myself/media-select',
        'media-select.upload': 'myself/media-select.upload',
    }
})
loadModules(['media-select', 'media-select.upload'])
```