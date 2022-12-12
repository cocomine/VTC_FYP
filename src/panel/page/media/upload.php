<?php
/*
 * Copyright (c) 2022.
 * Create by cocomine
 */

namespace panel\page\media;


use cocomine\IPage;
use mysqli;

class upload implements IPage {
    private mysqli $sqlcon;
    private array $UpPath;

    function __construct(mysqli $sqlcon, array $UpPath) {
        $this->sqlcon = $sqlcon;
        $this->UpPath = $UpPath;
    }

    public function access(bool $isAuth, int $role, bool $isPost): int {
        return 200;
        /*if(!$isAuth) return 401;
        if($role < 2) return 403;
        return 200;*/
    }

    public function get_Title(): string {
        return showText("Media-upload.Title");
    }

    public function get_Head(): string {
        return showText("Media-upload.Head");
    }

    /**
     * path輸出
     * @return string 輸出
     */
    function path(): string {
        return "<li><a href='/panel'>" . showText("index.home") . "</a></li>
                        <li><span>" . showText("Media-upload.Head") . "</span></li>";
    }

    /**
     * 回傳表單資料
     * @return array 彈出窗口
     */
    function post(array $data): array {
        return array();
    }

    /**
     * 輸出頁面
     * @return string 輸出頁面內容
     */
    function showPage(): string {

        $Text = showText("Media-upload.Content"); //文件翻譯

        return <<<body
<div class='col-12 mt-4'>
    <div class='card'>
        <div class='card-body'>
            <h1 class='header-title'>{$Text['upload']}</h1>
            <div id='drop-area' class='row py-5 justify-content-center'>
                <h5 class='col-auto'>{$Text['drag']}</h5>
                <div class='w-100'></div>
                <p class='col-auto'>{$Text['or']}</p>
                <div class='w-100'></div>
                <div class='col-12 col-sm-4 '>
                    <input type='file' class='form-control' id='file-sel' multiple accept='image/jpeg,image/png,image/webp,image/gif' />
                    <label for="file-sel" class="form-label">{$Text['limit_type']}</label>
                </div>
            </div>
            <p>{$Text['limit']}</p>
            <ul class='list-group' id='file-upload-list'></ul>
        </div>
    </div>
</div>
<style>
    #drop-area{
        border: 5px dashed #ccc; 
        border-radius: 20px;
        transition: .5s;
    }
</style>
<script>
    loadModules(['myself/page/upload'])
</script>
body;
    }
}