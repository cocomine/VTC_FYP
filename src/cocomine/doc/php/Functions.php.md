Functions php library
===
雜項有用的function

#### `echo_error(int $code)`
> 作用: 輸出錯誤回應
```php
function echo_error(int $code)
```
  接受參數:
  + `int` $code 錯誤代碼<br>
    *  400 = 請求錯誤<br>
    *  401 = 需要授權<br>
    *  403 = 拒絕訪問<br>
    *  404 = 路徑錯誤<br>
    *  405 = 不接受請求方式<br>
    *  500 = 伺服器錯誤
    
---
#### `array_sanitize(array $array)`
> 作用: 過濾陣列字串
```php
function array_sanitize(array $array): array
```
接受參數:
+ `array` $array 需要過的濾陣列
  <br><br>

回傳: `array` 過濾後陣列

---
#### `VerifyEmail(string $email)`
> 作用: 檢查電郵格式
```php
function VerifyEmail(string $email): bool
```
接受參數:
+ string $email 電郵地址
  <br><br>

回傳: `bool` 是否正確

---
#### `EncodeHeader(string $str)`
> 作用: utf-8轉換(棄用)
```php
function EncodeHeader(string $str): string
```
接受參數:
+ `string` $str 需要轉換的字串
  <br><br>

回傳: `string` 轉換後字串

---
#### `Generate_Code(int $length)`
> 作用: 產生亂數
```php
function Generate_Code(int $length = 32): string
```
接受參數:
+ `int` $length 需要的長度, 預設32位
  <br><br>

回傳: `string` 亂數

---
#### `getCity(string $ip)`
> 作用: 取得城市
```php
function getCity(string $ip = null): string
```
接受參數:
+ `string|null` $ip IP地址, null = 會自動獲取當前會話ip
<br><br>

回傳: `string` 回傳城市資料

---
#### `getISP(string $ip)`
> 作用: 取得ISP
```php
function getISP(string $ip = null): string
```
接受參數:
+ `string|null` $ip IP地址, null = 會自動獲取當前會話ip
  <br><br>

回傳: `string` 回傳ISP資料

---
#### `getBrowser(string $user_agent)`
> 作用: 取得瀏覽器
```php
function getBrowser(string $user_agent = null)
```
  接受參數:
  + `string|null` $user_agent 用戶資料, 由http header取得, null = 自動索取
    <br><br>
  
  回傳: `array|false|object` 回傳Browscap資料

---
#### `SendMail(string $To, string $html, int $type, mysqli $sqlcon, string $subject)`
> 作用: 發送電郵,電郵隊列
```php
function SendMail(string $To, string $html, int $type, mysqli $sqlcon, string $subject = null): bool
```
接受參數:
+  `string` $To 收件者電郵
*  `string` $html 內容
*  `int` $type 類型 , 請輸入0
*  `mysqli` $sqlcon sql連結
*  `string` $subject 主旨
   <br><br>

回傳: `bool` 是否已經放入隊列
