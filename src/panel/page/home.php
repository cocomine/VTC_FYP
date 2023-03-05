<?php
/**
 * Copyright (c) 2020.
 * Create by cocomine
 */

namespace panel\page;

use cocomine\IPage;
use mysqli;

/**
 * Class home
 * @package cocopixelmc\Page
 */
class home implements IPage {
    private mysqli $sqlcon;
    private int $role;

    /**
     * home constructor.
     * sql連接
     * @param $sqlcon
     */
    function __construct($sqlcon) {
        $this->sqlcon = $sqlcon;
    }

    /* 是否有權進入 */
    function access(bool $isAuth, int $role, bool $isPost): int {
        $this->role = $role;
        if(!$isAuth) return 401;
        if($role < 1) return 403;
        return 200;
    }

    /* 輸出頁面 */
    function showPage(): string {

        $Text = showText('index.Content');

        /* json 語言 */
        $jsonLang = json_encode(array());

        if($this->role < 2){
            return "<script>location.replace('/')</script>";
        }else{
            return <<<body
<pre id='langJson' style='display: none'>$jsonLang</pre>
<div class='col-12 mt-4'>

</div>
<script>
loadModules(['myself/datepicker', 'myself/page/home'])
</script>
body;
        }

    }

    /* POST請求 */
    function post(array $data): array {
        global $auth;

        return array();
    }

    /* path輸出 */
    function path(): string {
        return "<li><span>" . showText("index.home") . "</span></li>";
    }

    /* 取得頁面標題 */
    public function get_Title(): string {
        return showText('index.title');
    }

    /* 取得頁首標題 */
    public function get_Head(): string {
        return showText("index.home");
    }
}