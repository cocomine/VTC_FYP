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
     */
    public function access(bool $isAuth, int $role):int;

    /**
     * Get 請求
     * 用來讀取資源
     * @return array 回傳資料
     */
    public function get():array;

    /**
     * Post 請求
     * 用來創建資源
     * @param array $data 收到資料
     * @return array 回傳資料
     */
    public function post(array $data):array;

    /**
     * Put 請求
     * 用來修改資源
     * @param array $data 收到資料
     * @return array 回傳資料
     */
    public function put(array $data):array;

    /**
     * Delete 請求
     * 用來刪除資源
     * @return array 回傳資料
     */
    public function delete():array;
}