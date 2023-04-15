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
 * @link https://github.com/cocomine/VTC_FYP/blob/master/src/cocomine/doc/php/IPage.php.md
 */
interface IPage {

    /**
     * 是否有權進入
     * @param bool $isAuth 是否已登入
     * @param int $role 身份組
     * @param bool $isPost 是否post請求
     * @return int 授權狀態<br>
     *  401 => 需要登入<br>
     *  403 => 不可訪問<br>
     *  404 => 找不到<br>
     *  200 => 可以訪問<br>
     *  500 => 伺服器錯誤
     * @link https://github.com/cocomine/VTC_FYP/blob/master/src/cocomine/doc/php/IPage.php.md#accessbool-isauth-int-role-bool-ispost
     */
    public function access(bool $isAuth, int $role, bool $isPost): int;

    /**
     * 輸出頁面
     * @return string 輸出頁面內容
     * @link https://github.com/cocomine/VTC_FYP/blob/master/src/cocomine/doc/php/IPage.php.md#showpage
     */
    public function showPage(): string;

    /**
     * POST請求
     * @param array $data json數據
     * @return array 返回內容
     * @link https://github.com/cocomine/VTC_FYP/blob/master/src/cocomine/doc/php/IPage.php.md#postarray-data
     */
    public function post(array $data):array;

    /**
     * path html輸出
     * @return string html輸出
     * @link https://github.com/cocomine/VTC_FYP/blob/master/src/cocomine/doc/php/IPage.php.md#path
     */
    public function path(): string;

    /**
     * 取得頁面標題
     * @return string 頁面標題
     * @link https://github.com/cocomine/VTC_FYP/blob/master/src/cocomine/doc/php/IPage.php.md#get_title
     */
    public function get_Title(): string;

    /**
     * 取得頁首標題
     * @return string 頁首標題
     * @link https://github.com/cocomine/VTC_FYP/blob/master/src/cocomine/doc/php/IPage.php.md#get_head
     */
    public function get_Head(): string;

    /**
     * 取得頁面描述
     * @return string 頁面描述
     */
    public function get_description(): ?string;

    /**
     * 取得頁面圖片
     * @return string 頁面圖片
     */
    public function get_image(): ?string;
}