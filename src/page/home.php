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
<style>
.card-img-top {
    width: 300px;
    height: 300px;
    clip: rect(10px,290px,290px,10px);
}
</style>
<div class='col-12 mt-12'>
    <div id="carouselExample" class="carousel slide">
      <div class="carousel-inner">
        <div class="carousel-item active">
          <img src="/assets/images/background/hot-air-balloon-back.jpg" class="d-block w-100" alt="Welcome to X-Travel">
          <div class="carousel-caption d-none d-md-block">
            <h5>Explore places and other experiences</h5>
    
            <div class="dropdown">
              <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" data-bs-auto-close="outside">
                Choose Location Or Activities
              </button>
              <form class="dropdown-menu p-4">
                <div class="mb-3">
                    <div class="search-box">
                        <input type="text" name="search" placeholder="Search Location or Activities" required>
                        <i class="ti-search"></i>
                  </div>
                </div>
                <div class="mb-3">
                  <div class="btn-group-vertical" role="group" aria-label="Vertical button group">
                      <div class="btn-group dropend">
                          <button type="button" class="btn btn-light dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            Hong Kong
                          </button>
                          <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#">Canoeing</a></li>
                            <li><a class="dropdown-item" href="#">Climbing</a></li>
                            <li><a class="dropdown-item" href="#">Diving</a></li>
                            <li><a class="dropdown-item" href="#">Paragliding</a></li>
                            <li><a class="dropdown-item" href="#">Trekking</a></li>
                          </ul>
                      </div>
                      
                      <div class="btn-group dropend">
                          <button type="button" class="btn btn-light dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            China
                          </button>
                          <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#">Canoeing</a></li>
                            <li><a class="dropdown-item" href="#">Climbing</a></li>
                            <li><a class="dropdown-item" href="#">Hot air balloon flight</a></li>
                            <li><a class="dropdown-item" href="#">Mountaineering</a></li>
                            <li><a class="dropdown-item" href="#">Paragliding</a></li>
                            <li><a class="dropdown-item" href="#">Skiing</a></li>
                            <li><a class="dropdown-item" href="#">Trekking</a></li>
                          </ul>
                      </div>
                      
                      <div class="btn-group dropend">
                          <button type="button" class="btn btn-light dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            Macau
                          </button>
                          <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#">Bungee Jumping</a></li>
                            <li><a class="dropdown-item" href="#">Climbing</a></li>
                          </ul>
                      </div>
                      
                      <div class="btn-group dropend">
                          <button type="button" class="btn btn-light dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            Taiwan
                          </button>
                          <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#">Canoeing</a></li>
                            <li><a class="dropdown-item" href="#">Climbing</a></li>
                            <li><a class="dropdown-item" href="#">Diving</a></li>
                            <li><a class="dropdown-item" href="#">Mountaineering</a></li>
                            <li><a class="dropdown-item" href="#">Parachute</a></li>
                            <li><a class="dropdown-item" href="#">Paragliding</a></li>
                            <li><a class="dropdown-item" href="#">Trekking</a></li>
                          </ul>
                      </div>
                  </div>
                </div>
              </form>
            </div>      
          </div>
        </div>
      </div>
    </div>
</div></br>

<div class="home-demo">
  <div class="row">
    <div class="large-12 columns">
      <h3><b>香港地區最新活動</b></h3></br>
      <div class="owl-carousel">
        <div class="item">
          <div class="card card-block mx-2" style="min-width: 300px;">
            <img src="/assets/images/event/Canoeing_Hong_Kong_01.jpg" class="card-img-top" alt="...">
            <div class="card-body">
              <h5 class="card-title">Seaer Canoeing Adventure</h5>
              <p class="card-text">Summer water activities.</p>
              <a href="#" class="btn btn-primary stretched-link">Register Now</a>
            </div>
          </div>
        </div>
        <div class="item">
          <div class="card card-block mx-2" style="min-width: 300px;">
            <img src="/assets/images/event/Climbing_Hong_Kong_01.jpg" class="card-img-top" alt="...">
            <div class="card-body">
              <h5 class="card-title">CampFive Climbing Funday</h5>
              <p class="card-text">Rock climbing along the beautiful harbor.</p>
              <a href="#" class="btn btn-primary stretched-link">Register Now</a>
            </div>
          </div>
        </div>
        <div class="item">
          <div class="card card-block mx-2" style="min-width: 300px;">
            <img src="/assets/images/event/Diving_Hong_Kong_01.jpg" class="card-img-top" alt="...">
            <div class="card-body">
              <h5 class="card-title">UnderSea Diving</h5>
              <p class="card-text">The beautiful seabed of Hong Kong.</p>
              <a href="#" class="btn btn-primary stretched-link">Register Now</a>
            </div>
          </div>
        </div>
        <div class="item">
          <div class="card card-block mx-2" style="min-width: 300px;">
            <img src="/assets/images/event/Climbing_Hong_Kong_02.jpg" class="card-img-top" alt="...">
            <div class="card-body">
              <h5 class="card-title">FunUp Climbing Funday</h5>
              <p class="card-text">Rock climbing in Hong Kong's super volcano.</p>
              <a href="#" class="btn btn-primary stretched-link">Register Now</a>
            </div>
          </div>
        </div>
        <div class="item">
          <div class="card card-block mx-2" style="min-width: 300px;">
            <img src="/assets/images/event/Canoeing_Hong_Kong_02.jpg" class="card-img-top" alt="...">
            <div class="card-body">
              <h5 class="card-title">JoJo Canoeing</h5>
              <p class="card-text">Cooling off event.</p>
              <a href="#" class="btn btn-primary stretched-link">Register Now</a>
            </div>
          </div>
        </div>
        <div class="item">
          <div class="card card-block mx-2" style="min-width: 300px;">
            <img src="/assets/images/event/Trekking_Hong_Kong_01.jpg" class="card-img-top" alt="..." width="300" height="200">
            <div class="card-body">
              <h5 class="card-title">Kiver Hiking</h5>
              <p class="card-text">Enjoy Hong Kong Hiking Trails.</p>
              <a href="#" class="btn btn-primary stretched-link">Register Now</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="home-demo">
  <div class="row">
    <div class="large-12 columns">
      <h3><b>中國地區最新活動</b></h3></br>
      <div class="owl-carousel">
        <div class="item">
          <div class="card card-block mx-2" style="min-width: 300px;">
            <img src="/assets/images/event/Mountaineering_China_01.jpg" class="card-img-top" alt="...">
            <div class="card-body">
              <h5 class="card-title">Haba Adventure</h5>
              <p class="card-text">Guide you to the summit.</p>
              <a href="#" class="btn btn-primary stretched-link">Register Now</a>
            </div>
          </div>
        </div>
        <div class="item">
          <div class="card card-block mx-2" style="min-width: 300px;">
            <img src="/assets/images/event/Climbing_China_01.jpg" class="card-img-top" alt="...">
            <div class="card-body">
              <h5 class="card-title">Yangshuo Rock Family</h5>
              <p class="card-text">Feel the beauty of China's rock climbing holy land.</p>
              <a href="#" class="btn btn-primary stretched-link">Register Now</a>
            </div>
          </div>
        </div>
        <div class="item">
          <div class="card card-block mx-2" style="min-width: 300px;">
            <img src="/assets/images/event/Canoeing_China_01.jpg" class="card-img-top" alt="...">
            <div class="card-body">
              <h5 class="card-title">Guilin Canoeing Association</h5>
              <p class="card-text">The beautiful View of Cuilin.</p>
              <a href="#" class="btn btn-primary stretched-link">Register Now</a>
            </div>
          </div>
        </div>
        <div class="item">
          <div class="card card-block mx-2" style="min-width: 300px;">
            <img src="/assets/images/event/Skiing_China_01.jpg" class="card-img-top" alt="...">
            <div class="card-body">
              <h5 class="card-title">Beijing Ski</h5>
              <p class="card-text">Best Ski Place in China.</p>
              <a href="#" class="btn btn-primary stretched-link">Register Now</a>
            </div>
          </div>
        </div>
        <div class="item">
          <div class="card card-block mx-2" style="min-width: 300px;">
            <img src="/assets/images/event/Canoeing_China_02.jpg" class="card-img-top" alt="...">
            <div class="card-body">
              <h5 class="card-title">China Dragon Canoeing</h5>
              <p class="card-text">Best Canoeing in China.</p>
              <a href="#" class="btn btn-primary stretched-link">Register Now</a>
            </div>
          </div>  
        </div>
      </div>
    </div>
  </div>
</div>

<div class="home-demo">
  <div class="row">
    <div class="large-12 columns">
      <h3><b>澳門地區最新活動</b></h3></br>
      <div class="owl-carousel">
        <div class="item">
          <div class="card card-block mx-2" style="min-width: 300px;">
            <img src="/assets/images/event/Jump_Macau_01.jpeg" class="card-img-top" alt="...">
            <div class="card-body">
              <h5 class="card-title">Jump Adventure</h5>
              <p class="card-text">Guide you to the jump.</p>
              <a href="#" class="btn btn-primary stretched-link">Register Now</a>
            </div>
          </div>
        </div>
        <div class="item">
          <div class="card card-block mx-2" style="min-width: 300px;">
            <img src="/assets/images/event/Climbing_Macau_01.jpg" class="card-img-top" alt="...">
            <div class="card-body">
              <h5 class="card-title">Solution Gym</h5>
              <p class="card-text">Feel the bouldering.</p>
              <a href="#" class="btn btn-primary stretched-link">Register Now</a>
            </div>
          </div>
        </div>
        <div class="item">
          <div class="card card-block mx-2" style="min-width: 300px;">
            <img src="/assets/images/event/Climbing_Macau_02.jpg" class="card-img-top" alt="...">
            <div class="card-body">
              <h5 class="card-title">Macau's Crux</h5>
              <p class="card-text">Feel the climbing.</p>
              <a href="#" class="btn btn-primary stretched-link">Register Now</a>
            </div>
          </div>
        </div>
        <div class="item">
          <div class="card card-block mx-2" style="min-width: 300px;">
            <img src="/assets/images/event/Climbing_Macau_03.png" class="card-img-top" alt="...">
            <div class="card-body">
              <h5 class="card-title">Zel Climb</h5>
              <p class="card-text">Good relax place in Macau.</p>
              <a href="#" class="btn btn-primary stretched-link">Register Now</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="home-demo">
  <div class="row">
    <div class="large-12 columns">
      <h3><b>台灣地區最新活動</b></h3></br>
      <div class="owl-carousel">
        <div class="item">
          <div class="card card-block mx-2" style="min-width: 300px;">
            <img src="/assets/images/event/Climbing_Taiwan_01.jpeg" class="card-img-top" alt="...">
            <div class="card-body">
              <h5 class="card-title">Dragon Hole Adventure</h5>
              <p class="card-text">Guide you to climb in Taiwan.</p>
              <a href="#" class="btn btn-primary stretched-link">Register Now</a>
            </div>
          </div>
        </div>
        <div class="item">
          <div class="card card-block mx-2" style="min-width: 300px;">
            <img src="/assets/images/event/Diving_Taiwan_01.jpg" class="card-img-top" alt="...">
            <div class="card-body">
              <h5 class="card-title">Blue Sea Gym</h5>
              <p class="card-text">Enjoy the blue sea.</p>
              <a href="#" class="btn btn-primary stretched-link">Register Now</a>
            </div>
          </div>
        </div>
        <div class="item">
          <div class="card card-block mx-2" style="min-width: 300px;">
            <img src="/assets/images/event/Climbing_Taiwan_02.jpeg" class="card-img-top" alt="...">
            <div class="card-body">
              <h5 class="card-title">Boost Gym</h5>
              <p class="card-text">Play hard, climb hard.</p>
              <a href="#" class="btn btn-primary stretched-link">Register Now</a>
            </div>
          </div>
        </div>
        <div class="item">
          <div class="card card-block mx-2" style="min-width: 300px;">
            <img src="/assets/images/event/Trekking_Taiwan_01.jpg" class="card-img-top" alt="...">
            <div class="card-body">
              <h5 class="card-title">Taiwan Mountaineering Club</h5>
              <p class="card-text">Hiking, trekking and mountaineering in Taiwan.</p>
              <a href="#" class="btn btn-primary stretched-link">Register Now</a>
            </div>
          </div>
        </div>
        <div class="item">
          <div class="card card-block mx-2" style="min-width: 300px;">
            <img src="/assets/images/event/Diving_Taiwan_02.jpg" class="card-img-top" alt="...">
            <div class="card-body">
              <h5 class="card-title">See to Sea Club</h5>
              <p class="card-text">View All Taiwan's Seabed.</p>
              <a href="#" class="btn btn-primary stretched-link">Register Now</a>
            </div>
          </div>
        </div>
        <div class="item">
          <div class="card card-block mx-2" style="min-width: 300px;">
            <img src="/assets/images/event/Paragliding_Taiwan_01.jpeg" class="card-img-top" alt="...">
            <div class="card-body">
              <h5 class="card-title">Sky Club</h5>
              <p class="card-text">Flying in the skies of taiwan.</p>
              <a href="#" class="btn btn-primary stretched-link">Register Now</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  var owl = $('.owl-carousel');
  owl.owlCarousel({
    margin: 10,
    loop: true,
    responsive: {
      0: {
        items: 1
      },
      600: {
        items: 2
      },
      1000: {
        items: 3
      }
    }
  })

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