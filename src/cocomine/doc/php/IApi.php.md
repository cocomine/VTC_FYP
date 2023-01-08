IApi php library
===
用作全域用途, 多個頁面使用一個php
> 需要遵守RESTful API標準(不嚴格), 對應操作對應http method, 結果狀態回應對應http status code

用法
---
#### `access(bool $isAuth, int $role, bool $isPost)`
  > 作用: 是否有權進入
  ```php
  public function access(bool $isAuth, int $role): int;
  ```
  參數:
    * `bool` $isAuth 是否已登入
    * `int` $role 身份組
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
#### `get()`
    > 作用: Get 請求, 用來讀取資源
    ```php
    public function get();
    ```

---
#### `post(array $data)`
    > 作用: Post 請求, 用來創建資源
    ```php
    public function post(array $data);
    ```
    參數:
     * `array|null` $data 收到資料, null = 非json資料,如:二進制檔案(圖片,影片,文件),所以用另外方式獲取
  
---
#### `put(array $data)`
    > 作用: Put 請求, 用來修改已存在的資源
    ```php
    public function put(array $data);
    ```
  參數:
    * `array|null` $data 收到資料, null = 非json資料,如:二進制檔案(圖片,影片,文件),所以用另外方式獲取

---
#### `delete(array $data)`
    > 作用: Delete 請求, 用來刪除已存在的資源
    ```php
    public function delete(array $data);
    ```
  參數:
    * `array|null` $data 收到資料, null = 非json資料,如:二進制檔案(圖片,影片,文件),所以用另外方式獲取
