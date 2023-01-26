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
            <div id="carouselExampleCaptions" class="carousel slide">
              <div class="carousel-indicators">
                <button type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
                <button type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide-to="1" aria-label="Slide 2"></button>
                <button type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide-to="2" aria-label="Slide 3"></button>
              </div>
              <div class="carousel-inner">
                <div class="carousel-item active">
                  <img src="/assets/images/background/skiing-back.jpg" class="d-block w-100" alt="Skiing">
                  <div class="carousel-caption d-none d-md-block">
                    <button type="button" class="btn btn-primary btn-lg">See More</button>
                    <h5>Into white world</h5>
                  </div>
                </div>
                <div class="carousel-item">
                  <img src="/assets/images/background/diving-back.jpg" class="d-block w-100" alt="Diving">
                  <div class="carousel-caption d-none d-md-block">
                    <button type="button" class="btn btn-primary btn-lg">See More</button>
                    <h5>Close to inhabitants of the sea</h5>
                  </div>
                </div>
                <div class="carousel-item">
                  <img src="/assets/images/background/climbing-back.jpg" class="d-block w-100" alt="Climbing">
                  <div class="carousel-caption d-none d-md-block">
                    <button type="button" class="btn btn-primary btn-lg">See More</button>
                    <h5>Rock and Roll</h5>
                  </div>
                </div>
              </div>
              <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
              </button>
              <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide="next">
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