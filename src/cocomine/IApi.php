<?php
/*
 * Copyright (c) 2022.
 * Create by cocomine
 */

namespace cocomine;

interface IApi {

    /**
     * 是否有權進入
     * @param bool $isAuth 是否已登入
     * @param int $role 身份組
     * @return int 授權狀態<br>
     *  401 => 需要登入<br>
     *  403 => 不可訪問<br>
     *  404 => 找不到<br>
     *  200 => 可以訪問<br>
     *  500 => 伺服器錯誤
     * @link https://github.com/cocomine/VTC_FYP/blob/master/src/cocomine/doc/php/IApi.php.md#accessbool-isauth-int-role-bool-ispost
     */
    public function access(bool $isAuth, int $role):int;

    /**
     * Get 請求
     * 用來讀取資源
     * @link https://github.com/cocomine/VTC_FYP/blob/master/src/cocomine/doc/php/IApi.php.md#get
     */
    public function get();

    /**
     * Post 請求
     * 用來創建資源
     * @param array|null $data 收到資料
     * @link https://github.com/cocomine/VTC_FYP/blob/master/src/cocomine/doc/php/IApi.php.md#postarray-data
     */
    public function post(?array $data);

    /**
     * Put 請求
     * 用來修改資源
     * @param array|null $data 收到資料
     * @link https://github.com/cocomine/VTC_FYP/blob/master/src/cocomine/doc/php/IApi.php.md#putarray-data
     */
    public function put(?array $data);

    /**
     * Delete 請求
     * 用來刪除資源
     * @param array|null $data 收到資料
     * @link https://github.com/cocomine/VTC_FYP/blob/master/src/cocomine/doc/php/IApi.php.md#deletearray-data
     */
    public function delete(?array $data);
}