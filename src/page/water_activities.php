<?php
/**
 * Copyright (c) 2020.
 * Create by cocomine
 */

namespace page;

use cocomine\IPage;
use mysqli;
use panel\apis\media;

/**
 * Class water_activites
 * @package cocopixelmc\Page
 */
class water_activities implements IPage {
    private mysqli $sqlcon;

    /**
     * home constructor.
     * sql連接
     * @param $sqlcon
     */
    function __construct($sqlcon) {
        $this->sqlcon = $sqlcon;
    }

    /**
     * @inheritDoc
     */
    public function access(bool $isAuth, int $role, bool $isPost): int
    {
        return 200;
    }

    public function showPage(): string
    {
        $Text = showText('index.Content');

        /* json 語言 */
        $jsonLang = json_encode(array());

        return <<<body
<link rel="stylesheet" href="/assets/css/myself/page/water_activities.css">
<pre id='langJson' style='display: none'>$jsonLang</pre>
<div id='homeBackground' class="position-relative">
    <div class="row justify-content-center align-items-center">
        <div class="col-auto">
            <h5>體驗刺激，享受不一樣的生活點滴</h5>
            <div class="dropdown">
              <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" data-bs-auto-close="outside">
              選擇地區或活動
              </button>
              <form class="dropdown-menu p-4">
                  <div class="mb-3">
                      <div class="search-box">
                          <input type="text" name="search" placeholder="搜尋地點或活動" required>
                          <i class="ti-search"></i>
                    </div>
                  </div>
                  <div class="mb-3">
                    <div class="btn-group-vertical" role="group" aria-label="Vertical button group">
                        <div class="btn-group dropend">
                            <button type="button" class="btn btn-light dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                              香港地區
                            </button>
                            <ul class="dropdown-menu">
                              <li><a class="dropdown-item" href="#">獨木舟</a></li>
                              <li><a class="dropdown-item" href="#">攀岩</a></li>
                              <li><a class="dropdown-item" href="#">潛水</a></li>
                              <li><a class="dropdown-item" href="#">滑翔傘</a></li>
                              <li><a class="dropdown-item" href="#">遠足</a></li>
                            </ul>
                        </div>
                        
                        <div class="btn-group dropend">
                            <button type="button" class="btn btn-light dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                              中國地區
                            </button>
                            <ul class="dropdown-menu">
                              <li><a class="dropdown-item" href="#">獨木舟</a></li>
                              <li><a class="dropdown-item" href="#">攀岩</a></li>
                              <li><a class="dropdown-item" href="#">熱氣球</a></li>
                              <li><a class="dropdown-item" href="#">登山</a></li>
                              <li><a class="dropdown-item" href="#">滑翔傘</a></li>
                              <li><a class="dropdown-item" href="#">滑雪</a></li>
                              <li><a class="dropdown-item" href="#">遠足</a></li>
                            </ul>
                        </div>
                        
                        <div class="btn-group dropend">
                            <button type="button" class="btn btn-light dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                              澳門地區
                            </button>
                            <ul class="dropdown-menu">
                              <li><a class="dropdown-item" href="#">笨豬跳</a></li>
                              <li><a class="dropdown-item" href="#">攀岩</a></li>
                            </ul>
                        </div>
                        
                        <div class="btn-group dropend">
                            <button type="button" class="btn btn-light dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                              台灣地區
                            </button>
                            <ul class="dropdown-menu">
                              <li><a class="dropdown-item" href="#">獨木舟</a></li>
                              <li><a class="dropdown-item" href="#">攀岩</a></li>
                              <li><a class="dropdown-item" href="#">潛水</a></li>
                              <li><a class="dropdown-item" href="#">登山</a></li>
                              <li><a class="dropdown-item" href="#">跳傘</a></li>
                              <li><a class="dropdown-item" href="#">滑翔傘</a></li>
                              <li><a class="dropdown-item" href="#">遠足</a></li>
                            </ul>
                        </div>
                    </div>
                  </div>
              </form>
            </div>      
        </div>
    </div>
</div>
body . <<<body
<script>
loadModules(['myself/datepicker', 'myself/page/water_activities'])
</script>
body;
    }

    public function post(array $data): array
    {
        global $auth;

        return array();
    }

    public function path(): string
    {
        return '<li class="breadcrumb-item active">水上活動</li>';
    }

    /**
     * @inheritDoc
     */
    public function get_Title(): string
    {
        return "水上活動|X-Travel";
    }

    /**
     * @inheritDoc
     */
    public function get_Head(): string
    {
        return "水上活動";
    }
}