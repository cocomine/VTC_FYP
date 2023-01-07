media-select javascript library
===

需要載入css檔案
```html
<link rel="stylesheet" href="/assets/css/myself/media-select.css">
```

多語言
---
要使用多種語言，您必須具有以下html
```html
<pre id="media-select-LangJson" class="d-none">以json格式放置文本</pre>
```
json必須符合結構
```json
{
  "No_media": "",
  "Media": "",
  "Unknown_Error": "",
  "title": "",
  "Select": ["", ""],
  "drag": ""
}
```
> 你可以參考第21到26行

用法
---
1. 載入插件 `media-select`
    ```javascript
    //HTML script tag內
    require.config({
        paths:{
            'media-select': 'myself/media-select',
        }
    })
    loadModules(['media-select'])
    ```
    ```javascript
    //js檔案內
    define(['jquery', 'media-select'], function (jq, media_select) {
        
    });
    ```

2. 接受選擇媒體 `media_select.select_media`
    ```javascript
    define(['jquery', 'media-select'], function (jq, media_select) {
        media_select.select_media(max, mime, function (ids){
            //ids = 用戶選擇的圖片id array
        });
    });
    ```
   將會有個彈出式視框讓用戶可以選擇
   > 參數:
   > * `max`; 類型`number`; 最多可選擇媒體, 0=無限
   > * `mime`; 類型`RegExp`; 列表過濾MIME媒體類型
   > * `selected_media`; 類型`function`; 回傳選擇媒體id

3. 處理緩存數據
   ```javascript
   //例子
    define(['jquery', 'media-select'], function (jq, media_select) {
        media_select.select_media(max, mime, function (ids){
            ids.forEach((id)=>{
                //type something...
            });
        });
    });
    ```

例子
---
```javascript
define(['jquery', 'media-select'], function (jq, media_select) {
    $('#select').click(function () {
        //用戶可以選擇無限將圖, 接受任何檔案類型
        media_select.select_media(0, /.*/, (ids) => {
            ids.forEach((id)=>{
                $('#show').append(`<img src="/panel/api/media/${id}" alt="${id}"/>`)
            })
        });
    })
})
```