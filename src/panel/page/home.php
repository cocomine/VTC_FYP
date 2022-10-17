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
class home implements IPage
{
    private mysqli $sqlcon;

    /**
     * home constructor.
     * sql連接
     * @param $sqlcon
     */
    function __construct($sqlcon){
        $this->sqlcon = $sqlcon;
    }

    /* 是否有權進入 */
    function access(bool $isAuth, int $role): int{
        return 200;
    }

    /* 輸出頁面 */
    function showPage(): string {
        return <<<body
        <div class='col-12 mt-4' style="height: 100vh">
            <div class="card h-100" style="background-image: url('/panel/assets/images/bg/bg/6.webp'); background-size: cover; background-position: center">
                <div class='card-body'>
                    <div class="row align-content-center h-100">
                        <div class="col-12 col-lg-8">
                            <div class="form-floating">
                                <input type="text" class="form-control form-rounded" id="Search" placeholder="Search flight">
                                <label for="Search">Search flight</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        body;
    }

    /* POST請求 */
    function post(array $data):array{
        return array();
    }

    /* path輸出 */
    function path(): string {
        return "<li><a href=\"/panel\" data-ajax=\"GET\">".showText("index.Console")."</a></li>
                        <li><span>".showText("index.home")."</span></li>";
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