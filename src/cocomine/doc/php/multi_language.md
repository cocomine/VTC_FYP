Multi Language
===
多語言系統

PHP 函數
---
```php
function showText(string $Path, string $localCode = null)
```
接受參數:
+ `string` $Path 路徑, json檔案對應路徑
+ `string|null` $localCode 語言代碼, 強制要求另一種語言翻譯

回傳: `string|array` 輸出文字, 如果對應路徑是文字則輸出字串, 否則輸出array

語言文件
---
> 所有語言文件均放在 `/Lang` 資料夾內, 文件名稱對應語言代碼, 關於語言代碼請參考: [語言代碼表](http://www.lingoes.cn/zh/translator/langcode.htm)

文件內部結構:
```json
{
  "Lang": "en",
  "Last_Update": "9/1/2023",
  "Translate": {
    /* 翻譯 */
  }
}
```
所有翻譯放在`Translate`裏面, `Lang`對應語言代碼, `Last_Update`文件最後更新日期

翻譯結構:
```json
{
  "Lang": "en",
  "Last_Update": "9/1/2023",
  "Translate": {
    "Account": { //<--自由填寫名稱
      "Title": "帳戶管理 | X-Travel",
      "Head": "帳戶管理",
      "Content": {
        /* 其他翻譯 */
      }
    }
  }
}
```
翻譯的內部結構並沒有任何格式要求但建議跟隨以上結構

`Account` 這裏的名稱自由填寫沒有任何要求, 
`Title`對應 [Ipage > get_Title()](./IPage.php.md#gettitle),
`Head`對應 [IPage > get_Head()](./IPage.php.md#gethead)

取得翻譯
---
當需要取得 Account 下面的 Title, 輸入
```php
showText('Account.Title') //會取得 string
```

當需要取得 Account 下面的 Content 所有資料, 輸入
```php
showText('Account.Content') //會取得 array
```