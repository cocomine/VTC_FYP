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
#hkCardList img {
    width: 300px;
    height: 200px;
}

#hkCardList a {
    position: absolute;
    top: 88%;
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
                            Macao
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
        
        
        <!--<div id="carouselExampleAutoplaying" class="carousel slide" data-bs-ride="carousel">
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
          <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleAutoplaying" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
          </button>
          <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleAutoplaying" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
          </button>
        </div>-->
</div></br>

<div class='col-12 mt-2'>
    <h2>Latest Activities in Hong Kong <span class="badge bg-secondary">New</span></h2></br>

    <div class="d-flex flex-row flex-nowrap overflow-auto" id="hkCardList">
        <div class="card card-block mx-2" style="min-width: 300px;">
          <img src="/assets/images/event/Canoeing_Hong_Kong_01.jpg" class="card-img-top" alt="...">
          <div class="card-body">
            <h5 class="card-title">Seaer Canoeing Adventure</h5>
            <p class="card-text">Summer water activities.</p>
            <a href="#" class="btn btn-primary stretched-link">Register Now</a>
          </div>
        </div>

        <div class="card card-block mx-2" style="min-width: 300px;">
          <img src="/assets/images/event/Climbing_Hong_Kong_01.jpg" class="card-img-top" alt="...">
          <div class="card-body">
            <h5 class="card-title">CampFive Climbing Funday</h5>
            <p class="card-text">Rock climbing along the beautiful harbor.</p>
            <a href="#" class="btn btn-primary stretched-link">Register Now</a>
          </div>
        </div>
        
        <div class="card card-block mx-2" style="min-width: 300px;">
          <img src="/assets/images/event/Diving_Hong_Kong_01.jpg" class="card-img-top" alt="...">
          <div class="card-body">
            <h5 class="card-title">UnderSea Diving</h5>
            <p class="card-text">The beautiful seabed of Hong Kong.</p>
            <a href="#" class="btn btn-primary stretched-link">Register Now</a>
          </div>
        </div>
        
        <div class="card card-block mx-2" style="min-width: 300px;">
          <img src="/assets/images/event/Climbing_Hong_Kong_02.jpg" class="card-img-top" alt="...">
          <div class="card-body">
            <h5 class="card-title">FunUp Climbing Funday</h5>
            <p class="card-text">Rock climbing in Hong Kong's super volcano.</p>
            <a href="#" class="btn btn-primary stretched-link">Register Now</a>
          </div>
        </div>
        
        <div class="card card-block mx-2" style="min-width: 300px;">
          <img src="/assets/images/event/Canoeing_Hong_Kong_02.jpg" class="card-img-top" alt="...">
          <div class="card-body">
            <h5 class="card-title">JoJo Canoeing</h5>
            <p class="card-text">Cooling off event.</p>
            <a href="#" class="btn btn-primary stretched-link">Register Now</a>
          </div>
        </div>
        
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