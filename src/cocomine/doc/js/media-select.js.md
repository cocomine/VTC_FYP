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

例子:
```php
$Text = showText('Media.Content');
$Text2 = showText('Media-upload.Content');

$LangJson = json_encode(array(
    'No_media'           => $Text['No_media'],
    'Media'              => $Text['Media'] . ' %s',
    'Unknown_Error'      => showText('Error'),
    'title' => $Text['Media_Select']['title'],
    'Select' => $Text['Media_Select']['Select'],
    'upload' => array(
        'Timeout'            => $Text2['respond']['Timeout'],
        'File_name_over'     => $Text2['respond']['File_name_over'],
        'Over_size'          => $Text2['respond']['Over_size'],
        'File_type_not_mach' => $Text2['respond']['File_type_not_mach'],
        'Waiting'            => $Text2['respond']['Waiting'],
        'limit_type' => $Text2['limit_type'],
        'drag' => $Text2['drag']
    )
));
```
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
        media_select.select_media(function (ids){
            //ids = 用戶選擇的圖片id array
        }, max, mime);
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
        media_select.select_media((ids) => {
            ids.forEach((id)=>{
                $('#show').append(`<img src="/panel/api/media/${id}" alt="${id}"/>`)
            }, 0, /.*/)
        });
    })
})
```