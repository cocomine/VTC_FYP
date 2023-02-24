<?php
/**
 * Copyright (c) 2020.
 * Create by cocomine
 */

namespace page;

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
<link rel="stylesheet" href="/assets/css/myself/page/home.css">
<pre id='langJson' style='display: none'>$jsonLang</pre>

<div class='container-fluid'>
  <div class="row justify-content-center align-items-center position-relative" id='homeBackground'>
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
body. <<<body
<div class="container mt-4">
    <div class="row gy-4">
    
      <div class="col-12">
        <h3><b>香港地區最新活動</b></h3></br>
        <div class="owl-carousel">
          <div class="item">
            <div class="card card-block mx-2" style="min-width: 300px;">
              <img src="/assets/images/event/Canoeing_Hong_Kong_01.jpg" class="card-img-top" alt="...">
              <div class="card-body">
                <h5 class="card-title">激海獨木舟探索</h5>
                <p class="card-text">炎炎夏日，最重要暢旺大海。</p>
                <a href="#" class="btn btn-primary stretched-link">了解更多</a>
              </div>
            </div>
          </div>
          <div class="item">
            <div class="card card-block mx-2" style="min-width: 300px;">
              <img src="/assets/images/event/Climbing_Hong_Kong_01.jpg" class="card-img-top" alt="...">
              <div class="card-body">
                <h5 class="card-title">「兄弟爬山」攀岩體驗</h5>
                <p class="card-text">在美麗的香港維海面前攀登。</p>
                <a href="#" class="btn btn-primary stretched-link">了解更多</a>
              </div>
            </div>
          </div>
          <div class="item">
            <div class="card card-block mx-2" style="min-width: 300px;">
              <img src="/assets/images/event/Diving_Hong_Kong_01.jpg" class="card-img-top" alt="...">
              <div class="card-body">
                <h5 class="card-title">海下世界潛水班</h5>
                <p class="card-text">在西貢海底中心呼喚愛。</p>
                <a href="#" class="btn btn-primary stretched-link">了解更多</a>
              </div>
            </div>
          </div>
          <div class="item">
            <div class="card card-block mx-2" style="min-width: 300px;">
              <img src="/assets/images/event/Climbing_Hong_Kong_02.jpg" class="card-img-top" alt="...">
              <div class="card-body">
                <h5 class="card-title">越嶺攀岩探索</h5>
                <p class="card-text">西貢糧船灣超級火山?我就攀!</p>
                <a href="#" class="btn btn-primary stretched-link">了解更多</a>
              </div>
            </div>
          </div>
          <div class="item">
            <div class="card card-block mx-2" style="min-width: 300px;">
              <img src="/assets/images/event/Canoeing_Hong_Kong_02.jpg" class="card-img-top" alt="...">
              <div class="card-body">
                <h5 class="card-title">JoJo 獨木舟體驗</h5>
                <p class="card-text">提供雙人獨木舟，情侶的夏天拍拖體驗。</p>
                <a href="#" class="btn btn-primary stretched-link">了解更多</a>
              </div>
            </div>
          </div>
          <div class="item">
            <div class="card card-block mx-2" style="min-width: 300px;">
              <img src="/assets/images/event/Trekking_Hong_Kong_01.jpg" class="card-img-top" alt="..." width="300" height="200">
              <div class="card-body">
                <h5 class="card-title">毅行遠足旅行團</h5>
                <p class="card-text">本地的遠足旅行團，帶你走足四大徑。</p>
                <a href="#" class="btn btn-primary stretched-link">了解更多</a>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-12">
        <h3><b>中國地區最新活動</b></h3></br>
        <div class="owl-carousel">
          <div class="item">
            <div class="card card-block mx-2" style="min-width: 300px;">
              <img src="/assets/images/event/Mountaineering_China_01.jpg" class="card-img-top" alt="...">
              <div class="card-body">
                <h5 class="card-title">哈巴雪山嚮導服務</h5>
                <p class="card-text">哈巴村的資深嚮導，帶你登上五千米雪山。</p>
                <a href="#" class="btn btn-primary stretched-link">了解更多</a>
              </div>
            </div>
          </div>
          <div class="item">
            <div class="card card-block mx-2" style="min-width: 300px;">
              <img src="/assets/images/event/Climbing_China_01.jpg" class="card-img-top" alt="...">
              <div class="card-body">
                <h5 class="card-title">陽朔攀岩社</h5>
                <p class="card-text">帶你逛逛中國攀岩聖地，一生人也未必攀得完。</p>
                <a href="#" class="btn btn-primary stretched-link">了解更多</a>
              </div>
            </div>
          </div>
          <div class="item">
            <div class="card card-block mx-2" style="min-width: 300px;">
              <img src="/assets/images/event/Canoeing_China_01.jpg" class="card-img-top" alt="...">
              <div class="card-body">
                <h5 class="card-title">桂林獨木舟協會</h5>
                <p class="card-text">沿河飽覽桂林甲天下的山水。</p>
                <a href="#" class="btn btn-primary stretched-link">了解更多</a>
              </div>
            </div>
          </div>
          <div class="item">
            <div class="card card-block mx-2" style="min-width: 300px;">
              <img src="/assets/images/event/Skiing_China_01.jpg" class="card-img-top" alt="...">
              <div class="card-body">
                <h5 class="card-title">北京軍刀山滑雪場</h5>
                <p class="card-text">北京最佳滑雪地，適合初學者、窮遊旅客。</p>
                <a href="#" class="btn btn-primary stretched-link">了解更多</a>
              </div>
            </div>
          </div>
          <div class="item">
            <div class="card card-block mx-2" style="min-width: 300px;">
              <img src="/assets/images/event/Canoeing_China_02.jpg" class="card-img-top" alt="...">
              <div class="card-body">
                <h5 class="card-title">中國龍獨木舟</h5>
                <p class="card-text">榮獲全中國最佳獨木舟服務獎項。</p>
                <a href="#" class="btn btn-primary stretched-link">了解更多</a>
              </div>
            </div>  
          </div>
        </div>
      </div>
  
      <div class="col-12">
        <h3><b>澳門地區最新活動</b></h3></br>
        <div class="owl-carousel">
          <div class="item">
            <div class="card card-block mx-2" style="min-width: 300px;">
              <img src="/assets/images/event/Jump_Macau_01.jpeg" class="card-img-top" alt="...">
              <div class="card-body">
                <h5 class="card-title">澳門旅遊塔笨豬跳</h5>
                <p class="card-text">你跳我跳大家跳。</p>
                <a href="#" class="btn btn-primary stretched-link">了解更多</a>
              </div>
            </div>
          </div>
          <div class="item">
            <div class="card card-block mx-2" style="min-width: 300px;">
              <img src="/assets/images/event/Climbing_Macau_01.jpg" class="card-img-top" alt="...">
              <div class="card-body">
                <h5 class="card-title">Solution 抱石館</h5>
                <p class="card-text">澳門高質室內抱石館，想爬一爬的你不妨一試。</p>
                <a href="#" class="btn btn-primary stretched-link">了解更多</a>
              </div>
            </div>
          </div>
          <div class="item">
            <div class="card card-block mx-2" style="min-width: 300px;">
              <img src="/assets/images/event/Climbing_Macau_03.png" class="card-img-top" alt="...">
              <div class="card-body">
                <h5 class="card-title">Macau's Crux 抱石館</h5>
                <p class="card-text">澳門路氹區內攀岩館，自認文青之餘又十分運動風的你一定要試。</p>
                <a href="#" class="btn btn-primary stretched-link">了解更多</a>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-12">
        <h3><b>台灣地區最新活動</b></h3></br>
        <div class="owl-carousel">
          <div class="item">
            <div class="card card-block mx-2" style="min-width: 300px;">
              <img src="/assets/images/event/Climbing_Taiwan_01.jpeg" class="card-img-top" alt="...">
              <div class="card-body">
                <h5 class="card-title">龍洞攀岩協會</h5>
                <p class="card-text">提供資深教練，帶你享受台灣最大面積戶外攀岩場。</p>
                <a href="#" class="btn btn-primary stretched-link">了解更多</a>
              </div>
            </div>
          </div>
          <div class="item">
            <div class="card card-block mx-2" style="min-width: 300px;">
              <img src="/assets/images/event/Diving_Taiwan_01.jpg" class="card-img-top" alt="...">
              <div class="card-body">
                <h5 class="card-title">藍天海潛水公司</h5>
                <p class="card-text">龜山島的碧海藍天，與綠海龜一起暢遊大海。</p>
                <a href="#" class="btn btn-primary stretched-link">了解更多</a>
              </div>
            </div>
          </div>
          <div class="item">
            <div class="card card-block mx-2" style="min-width: 300px;">
              <img src="/assets/images/event/Climbing_Taiwan_02.jpeg" class="card-img-top" alt="...">
              <div class="card-body">
                <h5 class="card-title">山緣抱石小館</h5>
                <p class="card-text">位於台北市的小區之內，能在攀岩之餘享受繁華都市中的一點寧靜。</p>
                <a href="#" class="btn btn-primary stretched-link">了解更多</a>
              </div>
            </div>
          </div>
          <div class="item">
            <div class="card card-block mx-2" style="min-width: 300px;">
              <img src="/assets/images/event/Trekking_Taiwan_01.jpg" class="card-img-top" alt="...">
              <div class="card-body">
                <h5 class="card-title">台灣登山俱樂部</h5>
                <p class="card-text">一條龍式為你辦理登山證、預訂山屋，帶你登上台灣最高。</p>
                <a href="#" class="btn btn-primary stretched-link">了解更多</a>
              </div>
            </div>
          </div>
          <div class="item">
            <div class="card card-block mx-2" style="min-width: 300px;">
              <img src="/assets/images/event/Diving_Taiwan_02.jpg" class="card-img-top" alt="...">
              <div class="card-body">
                <h5 class="card-title">「看海」潛水俱樂部</h5>
                <p class="card-text">提供全面式浮潛、水肺潛水服務，資深教練無時無刻陪伴你在大海中遊曆。</p>
                <a href="#" class="btn btn-primary stretched-link">了解更多</a>
              </div>
            </div>
          </div>
          <div class="item">
            <div class="card card-block mx-2" style="min-width: 300px;">
              <img src="/assets/images/event/Paragliding_Taiwan_01.jpeg" class="card-img-top" alt="...">
              <div class="card-body">
                <h5 class="card-title">台東天下滑翔傘公司</h5>
                <p class="card-text">提供兩小時的滑翔傘體驗服務，一睹台灣東部海岸景色。另有提供國際級標準滑翔傘訓練課程。</p>
                <a href="#" class="btn btn-primary stretched-link">了解更多</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
</div>
body. <<<body
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