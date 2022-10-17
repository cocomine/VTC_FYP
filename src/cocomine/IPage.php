<?php
/*
 * Copyright (c) 2022.
 * Create by cocomine
 */

namespace cocomine;

/**
 * 頁面方法界定
 * @package cocomine/IPage
 * @author cocomine<https://github.com/cocomine>
 * @version 1.0
 */
interface IPage {

    /**
     * 是否有權進入
     * @param bool $isAuth 是否已登入
     * @param int $role 身份組
     * @return int 授權狀態<br>
     *  401 => 需要登入<br>
     *  403 => 不可訪問<br>
     *  200 => 可以訪問
     */
    public function access(bool $isAuth, int $role): int;

    /**
     * 輸出頁面
     * @return string 輸出頁面內容
     */
    public function showPage(): string;

    /**
     * POST請求
     * @param array $data json數據
     * @return array 返回內容
     */
    function post(array $data):array;

    /**
     * path html輸出
     * @return string html輸出
     */
    function path(): string;

    /**
     * 取得頁面標題
     * @return string 頁面標題
     */
    public function get_Title(): string;

    /**
     * 取得頁首標題
     * @return string 頁首標題
     */
    public function get_Head(): string;
}