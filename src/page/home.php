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

        $hkActivities = '';

        $stmt = $this->sqlcon->prepare("SELECT ID, review, state, name, summary, country, thumbnail, create_time FROM Event WHERE review = 1 AND state = 1 AND country = 'HK' ORDER BY create_time DESC LIMIT 5");
        if (!$stmt->execute()) {
            return 'Database Error!';
        }

        $rs = $stmt->get_result();
        while($row = $rs->fetch_assoc()) {
            $hkActivities .= "<div class='item'><div class='card card-block mx-2' style='min-width: 300px;'><div class='ratio ratio-4x3 card-img-top overflow-hidden'>";
            $hkActivities .= "<img class='owl-lazy' data-src='panel/api/media/".$row['thumbnail']."' alt='".$row['thumbnail']."'></div><div class='card-body'>";
            $hkActivities .= "<h5 class='card-title'>".$row['name']."</h5>";
            $hkActivities .= "<p class='card-text'>".$row['summary']."</p>";
            $hkActivities .= "<a href='/details/".$row['ID']."' class='btn btn-primary stretched-link btn-rounded'>了解更多</a></div></div></div>";
        }

        $cnActivities = '';

        $stmt->prepare("SELECT ID, review, state, name, summary, country, thumbnail, create_time FROM Event WHERE review = 1 AND state = 1 AND country = 'CN' ORDER BY create_time DESC LIMIT 5");
        if (!$stmt->execute()) {
            return 'Database Error!';
        }

        $rs = $stmt->get_result();
        while($row = $rs->fetch_assoc()) {
            $cnActivities .= "<div class='item'><div class='card card-block mx-2' style='min-width: 300px;'><div class='ratio ratio-4x3 card-img-top overflow-hidden'>";
            $cnActivities .= "<img class='owl-lazy' data-src='panel/api/media/".$row['thumbnail']."' alt='".$row['thumbnail']."'></div><div class='card-body'>";
            $cnActivities .= "<h5 class='card-title'>".$row['name']."</h5>";
            $cnActivities .= "<p class='card-text'>".$row['summary']."</p>";
            $cnActivities .= "<a href='/details/".$row['ID']."' class='btn btn-primary stretched-link btn-rounded'>了解更多</a></div></div></div>";
        }

        $moActivities = '';

        $stmt->prepare("SELECT ID, review, state, name, summary, country, thumbnail, create_time FROM Event WHERE review = 1 AND state = 1 AND country = 'MO' ORDER BY create_time DESC LIMIT 5");
        if (!$stmt->execute()) {
            return 'Database Error!';
        }

        $rs = $stmt->get_result();
        while($row = $rs->fetch_assoc()) {
            $moActivities .= "<div class='item'><div class='card card-block mx-2' style='min-width: 300px;'><div class='ratio ratio-4x3 card-img-top overflow-hidden'>";
            $moActivities .= "<img class='owl-lazy' data-src='panel/api/media/".$row['thumbnail']."' alt='".$row['thumbnail']."'></div><div class='card-body'>";
            $moActivities .= "<h5 class='card-title'>".$row['name']."</h5>";
            $moActivities .= "<p class='card-text'>".$row['summary']."</p>";
            $moActivities .= "<a href='/details/".$row['ID']."' class='btn btn-primary stretched-link btn-rounded'>了解更多</a></div></div></div>";
        }

        $twActivities = '';

        $stmt->prepare("SELECT ID, review, state, name, summary, country, thumbnail, create_time FROM Event WHERE review = 1 AND state = 1 AND country = 'TW' ORDER BY create_time DESC LIMIT 5");
        if (!$stmt->execute()) {
            return 'Database Error!';
        }

        $rs = $stmt->get_result();
        while($row = $rs->fetch_assoc()) {
            $twActivities .= "<div class='item'><div class='card card-block mx-2' style='min-width: 300px;'><div class='ratio ratio-4x3 card-img-top overflow-hidden'>";
            $twActivities .= "<img class='owl-lazy' data-src='panel/api/media/".$row['thumbnail']."' alt='".$row['thumbnail']."'></div><div class='card-body'>";
            $twActivities .= "<h5 class='card-title'>".$row['name']."</h5>";
            $twActivities .= "<p class='card-text'>".$row['summary']."</p>";
            $twActivities .= "<a href='/details/".$row['ID']."' class='btn btn-primary stretched-link btn-rounded'>了解更多</a></div></div></div>";
        }
        return <<<body
<link rel="stylesheet" href="/assets/css/myself/page/home.css">
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
<div class="container mt-4">
    <div class="row gy-4">
    
      <div class="col-12">
        <h3><b>香港地區最新活動</b></h3></br>
        <div class="owl-carousel owl-theme"><!-- 呢度要留意要加返 'owl-theme' class -->
          $hkActivities
        </div>
      </div>

      <div class="col-12">
        <h3><b>中國地區最新活動</b></h3></br>
        <div class="owl-carousel owl-theme">
          $cnActivities
        </div>
      </div>
  
      <div class="col-12">
        <h3><b>澳門地區最新活動</b></h3></br>
        <div class="owl-carousel owl-theme">
          $moActivities
        </div>
      </div>

      <div class="col-12">
        <h3><b>台灣地區最新活動</b></h3></br>
        <div class="owl-carousel owl-theme">
          $twActivities
        </div>
      </div>
    </div>
</div>
body . <<<body
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
        return '<li class="breadcrumb-item active">' . showText("index.home") . '</li>';
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