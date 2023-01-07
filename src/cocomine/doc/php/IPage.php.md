IPage php library
===
用作顯示頁面, 一個頁面對應一個php

用法
---
+ `access(bool $isAuth, int $role, bool $isPost)`
  > 作用: 是否有權進入
  ```php
  public function access(bool $isAuth, int $role, bool $isPost): int;
  ```
  參數:
    * `bool` $isAuth 是否已登入
    * `int` $role 身份組
    * `bool` $isPost 是否post請求
  <br><br>

  接受回傳: `int` 授權狀態<br>
  接受數值:
    *  401 => 需要登入<br>
    *  403 => 不可訪問<br>
    *  404 => 找不到<br>
    *  200 => 可以訪問<br>
    *  500 => 伺服器錯誤
  <br><br>

  例子:
  沒有登入回傳401, 身份組低於2回傳403, 其餘通行回傳200
  ```php
  public function access(bool $isAuth, int $role, bool $isPost): int {
    if (!$isAuth) return 401;
    if ($role < 2) return 403;
    return 200;
  }
  ```

---
+ `showPage()`
  > 作用: 輸出頁面
  ```php
  public function showPage(): string;
  ```
  接受回傳: `string` 輸出頁面內容(HTML)
  <br><br>

  例子:
  ```php
  function showPage(): string {
    global $auth; //獲取全域變數 $auth, class: cocomine/MyAuth
    $userdata = $auth->userdata; //獲取用戶資訊
  
    /* php邏輯處理, type something... */
    return <<<body
        <!-- 在這裏輸入 HTML element -->
        
        <!-- 載入JS -->
        <script>
            <!-- 載入外部JS -->
            require.config({
              paths:{
                  zxcvbn: ['https://cdnjs.cloudflare.com/ajax/libs/zxcvbn/4.4.2/zxcvbn'],
                  forge: ['https://cdn.jsdelivr.net/npm/node-forge/dist/forge.min'],
                  FileSaver: ['FileSaver.min'],
              },
            });
            <!-- 載入外部JS End -->
  
            loadModules(['myself/page/ChangeSetting', 'zxcvbn', 'forge', 'FileSaver'])
        </script>
    body;
  }
  ```

---
+ `post(array $data)`
  > 作用: POST請求, 瀏覽器必須使用json數據格式傳送資料
  ```php
  public function post(array $data):array;
  ```
  參數:
  * `array` $data json數據
  <br><br>

  接受回傳: `array` 返回內容,會自動編譯做json
  <br><br>

  例子:
  ```php
  public function post(array $data):array{
    global $auth; //獲取全域變數 $auth, class: cocomine/MyAuth
    
    $_GET[...] // 取得url問號後的參數
    $data[...] // 取得post資料
  }
  ```

---
+ `path()`
  > 作用: 頁面路徑, html輸出
  ```php
  public function path(): string;
  ```
  接受回傳: `string` html輸出

---
+ `get_Title()`
  > 作用: 頁面標題, `<title>`element 內的文字
  ```php
  public function get_Title(): string;
  ```
  接受回傳: `string` 頁面標題

---
+ `get_Head()`
  > 作用: 頁首標題 (暫時沒有作用)
  ```php
  public function get_Head(): string;
  ```
  接受回傳: `string` 頁首標題