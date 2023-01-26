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
        return 200;
    }

    /* 輸出頁面 */
    function showPage(): string {

        $Text = showText('index.Content');

        /* json 語言 */
        $jsonLang = json_encode(array());

        return <<<body
<pre id='langJson' style='display: none'>$jsonLang</pre>
<div class='col-12 mt-4'>
    <div class="card">
        <div class="card-body">
            <div id="carouselExample" class="carousel slide">
              <div class="carousel-inner">
                <div class="carousel-item active">
                  <img src="/assets/images/background/skiing-back.jpg" class="d-block w-100" alt="Skiing">
                </div>
                <div class="carousel-item">
                  <img src="/assets/images/background/diving-back.jpg" class="d-block w-100" alt="Diving">
                </div>
                <div class="carousel-item">
                  <img src="/assets/images/background/climbing-back.jpg" class="d-block w-100" alt="Climbing">
                </div>
              </div>
              <button class="carousel-control-prev" type="button" data-bs-target="#carouselExample" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
              </button>
              <button class="carousel-control-next" type="button" data-bs-target="#carouselExample" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
              </button>
            </div>
        </div>
    </div>
</div>

<script>
loadModules(['myself/datepicker', 'myself/page/home'])
</script>
body;
    }

    /* POST請求 */
    function post(array $data): array {
        global $auth;

        return array();
    }

    /* path輸出 */
    function path(): string {
        return '<li class="breadcrumb-item active">'.showText("index.home").'</li>';
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