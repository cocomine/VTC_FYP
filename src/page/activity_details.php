<?php

namespace page;

use mysqli;
use cocomine\IPage;

class activity_details implements \cocomine\IPage {

    private array $UpPath;
    function __construct(mysqli $sqlcon, array $UpPath) {
        //$this->sqlcon = $sqlcon;
        $this->UpPath = $UpPath;
    }

    /**
     * @inheritDoc
     */
    public function access(bool $isAuth, int $role, bool $isPost): int {
        return 200;
    }

    /**
     * @inheritDoc
     */
    public function showPage(): string {

        $Text = showText('Media.Content');
        $Text2 = showText('Media-upload.Content');

        $LangJson = json_encode(array(
            'No_media'           => $Text['No_media'],
            'Media'              => $Text['Media'] . ' %s',
            'Unknown_Error'      => showText('Error'),
            'title' => $Text['Media_Select']['title'],
            'Select' => $Text['Media_Select']['Select'],
            'upload' => array(
                'Timeout'            => $Text2['respond']['Timeout'],
                'File_name_over'     => $Text2['respond']['File_name_over'],
                'Over_size'          => $Text2['respond']['Over_size'],
                'File_type_not_mach' => $Text2['respond']['File_type_not_mach'],
                'Waiting'            => $Text2['respond']['Waiting'],
                'limit_type' => $Text2['limit_type'],
                'drag' => $Text2['drag'],
                'upload' => $Text2['upload'],
                'or' => $Text2['or'],
                'limit' => $Text2['respond.']
            )
        ));

        return <<<body
    <link href="assets/css/myself/page/activity_details.css" rel="stylesheet">
    <meta charset="utf-8">
    <!-- main 1 -->
    <div class="row justify-content-center">
        <div class="col-10 gy-3">
            <div class="card">
                <!-- Carousel -->
                <div style="width:100%; float:right; " class="">
                    <div id="carouselExampleIndicators" class="carousel slide" data-bs-ride="true" style="padding: 20px">
                        <div class="carousel-indicators">
                            <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="0" class="active"
                                aria-current="true" aria-label="Slide 1"></button>
                            <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="1"
                                aria-label="Slide 2"></button>
                            <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="2"
                                aria-label="Slide 3"></button>
                        </div>
                        <div class="carousel-inner">
                            <div class="carousel-item active">
                                <img src="https://fyp.cocomine.cc/panel/api/media/jY4Rkm" class="d-block w-100" alt=""
                                    style="max-height:50vh;">
                            </div>
                            <div class="carousel-item">
                                <img src="https://fyp.cocomine.cc/panel/api/media/jY4Rkm" class="d-block w-100" alt=""
                                    style="max-height:50vh;">
                            </div>
                            <div class="carousel-item">
                                <img src="https://fyp.cocomine.cc/panel/api/media/jY4Rkm" class="d-block w-100" alt=""
                                    style="max-height:50vh;">
                            </div>
                        </div>
            
                        <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleIndicators"
                            data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Previous</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleIndicators"
                            data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Next</span>
                        </button>
                </div>
            </div>
        </div>
    </div>
        <!-- Carousel End-->
    
    </div>
    <!-- main 1 End -->
    
    <br />
    <!-- activity -->
     <div class="row justify-content-center">
        <div class="col-10 gy-3">
            <div class="center">
                <p class="title" >活動介紹</p>
                <div class="card-body">
                無論是在湖邊散步、慢跑，還是騎著腳踏遊覽車在湖邊遊覽，皆可飽覽迪欣湖恬靜優美的湖面風光，
                最適合一家大小一同來呼吸新鮮空氣，好好舒展身心。這個名為迪欣湖的人工湖有著波光粼粼的湖水、
                動感十足的噴泉和優美怡人的山景，絕對是你與摯愛重拾昔日樂趣、盡情玩樂的好地方。
                </div>
            </div>
        </div>
    </div>
    <!-- activity  End -->
    
    
    
    <!-- Noted -->
    
    <div class="row justify-content-center">
        <div class="col-10 gy-3">
            <div class="center">
                <p class="title">注意事項</p>
                <div class="card-body">
                溫馨提示：
                <ul style="list-style-type:disc">
                    <li>為保持社交距離，樂園會控制入園人數。你需在到訪樂園前90日內憑有效門票、會員卡、門票、換領憑證或確認通知預先透過預約到訪日子。</li>
                    <li>照片只供參考。度假區內所有活動、娛樂設施及表演安排會視乎實際情況而定，如有任何變動，恕不另行通知。請瀏覽香港迪士尼樂園度假區的官方網頁及手機應用程式以查閱最新資訊或時間表，以便輕鬆出行開展奇妙旅程</li>
                </ul>
                </div>
            </div>
        </div>
    </div>
    <!-- Noted End -->
    
    <!-- Price Main -->
    <div class="row justify-content-center">
        <div class="col-10 gy-3">
           <div class="center">
               <p class="title">預訂活動</p>
               <div class="Div_Price_activity_numb">
                   <div class="date-picker date-picker-inline">
                      <input type="date" class="date-picker-toggle form-control-lg" id="datePicker" min="">
                      <div class="date-calendar"></div>
                   </div>
                   <br/><br/>
                   <span style="font-size: 3vh"><b>計劃</b></span>
                   <div class="Div_Plus_Minus">
                        <button class="btn btn-outline-primary" id="ti-plus"><i class="fa fa-plus" aria-hidden="true"></i></button>
                       <span class="txt_Num">1</span>
                       <button class="btn btn-outline-primary" id="ti-minus"><i class="fa fa-minus" aria-hidden="true"></i></button>
                   </div>
               </div>
               <span class="Price_Sign">$</span><span class="Price_txt">150</span>
               <button type="button" class="btn btn-primary btn-lg"
                   style="width:15vh; float:right;margin-right: 1vh;margin-bottom: 1vh" id="order">立刻預訂</button>
           </div>
        </div>
    </div>
    
    <!-- Price Main End-->
    
    <!-- Event Details -->
    <div class="row justify-content-center">
        <div class="col-10 gy-3">
            <div class="center">
                <p class="title">活動摘要</p>
              <div class="card-body">
                迪欣湖活動中心，簡稱迪欣湖，鄰近香港廸士尼樂園，在迪欣湖，既可欣賞大自然優美景色，
                同時亦可享受活動中心提供的各項活動服務及配套，的確是香港少有遠離繁囂的休閒地點。
                周末不少人到迪欣湖影相打卡，除了model外，還有很多是拍婚紗照的新人。近來仲有不少人在迪欣湖這個背山面湖的草地上享野餐樂添！
              </div>
            </div>
        </div>
    </div>
    <!-- Event Details End -->
    
    
    <br />
    <!-- main 3 -->
    <div class="row justify-content-center">
        <div class="col-10 gy-3">
            <div class="center">
                <p class="title" id="map_title">地圖位置</p>
                <img src="/assets/images/icon/Logo-big.png" class="d-block w-100" width="500" height="800" style="padding: 30px;">
            </div>
        </div>
    </div>
    <!-- main 3 End-->
    
    
    
    <br />
    <!-- main 4 -->
    <div class="row justify-content-center">
        <div class="col-10 gy-3">
            <div class="center">
                <p class="title">評價</p>
                <!-- rating -->
                <div class="rating">
                    <p class="Rating_Number">4.5</p><br />
                    <p class="Rating_Number_maximum">/5.0</p>
                    <fieldset class="rate">
                        <label for="rating10" title="5 stars" style=""></label>
                        <label class="half" for="rating9" title="4 1/2 stars" style="color: #F09B0A;"></label>
                        <label for="rating8" title="4 stars" style="color: #F09B0A;"></label>
                        <label class="half" for="rating7" title="3 1/2 stars" style="color: #F09B0A;"></label>
                        <label for="rating6" title="3 stars" style="color: #F09B0A;"> </label>
                        <label class="half" for="rating5" title="2 1/2 stars" style="color: #F09B0A;"></label>
                        <label for="rating4" title="2 stars" style="color: #F09B0A;"></label>
                        <label class="half" for="rating3" title="1 1/2 stars" style="color: #F09B0A;"></label>
                        <label for="rating2" title="1 star" style="color: #F09B0A;"></label>
                        <label class="half" for="rating1" title="1/2 star" style="color: #F09B0A;"></label>
                </div>
                <!-- rating End -->
                <br />
            
                <!-- Comment_Selection -->
                <div class="button_Comment_Selection">
                    <button type="button" class="btn btn-outline-secondary" style="margin-right: 10px;" id="bt_all">全部</button>
                    <button type="button" class="btn btn-outline-secondary" style="margin-right: 10px;" id="bt_over_4">4.0+</button>
                    <button type="button" class="btn btn-outline-secondary" style="margin-right: 10px;" id="bt_over_3">3.0+</button>
                    <button type="button" class="btn btn-outline-secondary" style="margin-right: 10px;" id="bt_less_3"><3.0</button>
            
            
                    <div class="dropdown" style="float: right;">
                        <a class="btn btn-secondary dropdown-toggle" role="button" data-bs-toggle="dropdown" aria-expanded="false"
                            style="background-color:#FFFFFF;color:#6C757D;">
                            推薦
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item">最新</a></li>
                            <li><a class="dropdown-item">評價:高到降</a></li>
                        </ul>
                    </div>
                </div>
                <!-- Comment_Selection End -->
            <br/><br/>
            
            <! -- Comment -->
                <div class="card text-bg-light mb-3" style="max-width: 1000rem;" id="Comment_Card">
                    <div class="card-header">
                    <div  class="profile">
                       <!--img---->
                      <div class="profile-img"> 
                          <img src="https://fyp.cocomine.cc/panel/api/media/jY4Rkm" />
                      </div>
                      
                      <div class="name-user">
                          <strong>Touseeq Ijaz</strong>
                          <span>@touseeqijazweb</span>
                      </div>
                       <!--reviews------>
                      <div class="reviews">
                          <fieldset class="rate">
                              <label for="rating10" title="5 stars" style=""></label>
                              <label class="half" for="rating9" title="4 1/2 stars" style="color: #F09B0A;"></label>
                              <label for="rating8" title="4 stars" style="color: #F09B0A;"></label>
                              <label class="half" for="rating7" title="3 1/2 stars" style="color: #F09B0A;"></label>
                              <label for="rating6" title="3 stars" style="color: #F09B0A;"> </label>
                              <label class="half" for="rating5" title="2 1/2 stars" style="color: #F09B0A;"></label>
                              <label for="rating4" title="2 stars" style="color: #F09B0A;"></label>
                              <label class="half" for="rating3" title="1 1/2 stars" style="color: #F09B0A;"></label>
                              <label for="rating2" title="1 star" style="color: #F09B0A;"></label>
                              <label class="half" for="rating1" title="1/2 star" style="color: #F09B0A;"></label>
                      </div>
                    </div>
                    
                    </div>
                    <!----Comments-------------------------------------->
                    <div class="card-body">
                      <p class="card-text">
                        最近假日走去探望迪欣湖。
                        仿似同一個老朋友見面。發現已有好多港人走入迪欣湖野餐，拍家庭照及玩戶外活動。如是自駕遊人士，
                        可以將車輛泊在迪欣湖停車場。每小時$50。但迪欣湖只有23個車位。有部份自駕遊人士會在早上9時開始駛進。個人建議可以停泊在迪士尼樂園停車場。
                        日泊$260。白金卡、金卡及職員是免費泊車。迪欣湖暫停水上活動。只供家庭式單車出租。4人座每小時$110。6人座每小時$130。上午10時開始出租。
                        但往往在當天被遊人超額預訂。遊人只可以在當日即場預訂。無網上預訂服務。迪欣湖是一個休閒的湖泊。個人喜歡出門前去超巿買下麵包。在迪欣湖餵錦鯉。家長會自備帳篷。小朋友可以踏單車及踩roller。
                      </p>
                    </div>
                  </div>
            <! -- Comment End-->
            
            
            
            
                <! -- Comment Example 1 -->
                <div class="card text-bg-light mb-3" style="max-width: 1000rem;" id="card1">
                    <div class="card-header">
                    <div  class="profile">
                       <!--img---->
                      <div class="profile-img"> 
                          <img src="https://fyp.cocomine.cc/panel/api/media/jY4Rkm" />
                      </div>
                      
                      <div class="name-user">
                          <strong>Oliva</strong>
                          <span>@Olivaadward</span>
                      </div>
                       <!--reviews------>
                      <div class="reviews">
                          <fieldset class="rate">
                              <label for="rating10" title="5 stars" style=""></label>
                              <label class="half" for="rating9" title="4 1/2 stars" style=""></label>
                              <label for="rating8" title="4 stars" style="color: #F09B0A;"></label>
                              <label class="half" for="rating7" title="3 1/2 stars" style="color: #F09B0A;"></label>
                              <label for="rating6" title="3 stars" style="color: #F09B0A;"> </label>
                              <label class="half" for="rating5" title="2 1/2 stars" style="color: #F09B0A;"></label>
                              <label for="rating4" title="2 stars" style="color: #F09B0A;"></label>
                              <label class="half" for="rating3" title="1 1/2 stars" style="color: #F09B0A;"></label>
                              <label for="rating2" title="1 star" style="color: #F09B0A;"></label>
                              <label class="half" for="rating1" title="1/2 star" style="color: #F09B0A;"></label>
                      </div>
                    </div>
                    
                    </div>
                    <!----Comments-------------------------------------->
                    <div class="card-body">
                      <p class="card-text">
                        早排同左爸爸去咗迪欣湖個湖好靚仲有水上船仔踩仲有三人單車踩不過天氣好熱要飲多啲水仲有草地可以野餐附近仲有間七仔俾你買嘢食
                      </p>
                    </div>
                  </div>
            <! -- Comment Example 1 End-->
            
            
            
            
                <! -- Comment Example 2-->
                <div class="card text-bg-light mb-3" style="max-width: 1000rem;" id="card2">
                    <div class="card-header">
                    <div  class="profile">
                       <!--img---->
                      <div class="profile-img"> 
                          <img src="https://fyp.cocomine.cc/panel/api/media/jY4Rkm" />
                      </div>
                      
                      <div class="name-user">
                          <strong>Harry Potter</strong>
                          <span>@DanielRedclief</span>
                      </div>
                       <!--reviews------>
                      <div class="reviews">
                          <fieldset class="rate">
                              <label for="rating10" title="5 stars" style="color: #F09B0A;"></label>
                              <label class="half" for="rating9" title="4 1/2 stars" style="color: #F09B0A;"></label>
                              <label for="rating8" title="4 stars" style="color: #F09B0A;"></label>
                              <label class="half" for="rating7" title="3 1/2 stars" style="color: #F09B0A;"></label>
                              <label for="rating6" title="3 stars" style="color: #F09B0A;"> </label>
                              <label class="half" for="rating5" title="2 1/2 stars" style="color: #F09B0A;"></label>
                              <label for="rating4" title="2 stars" style="color: #F09B0A;"></label>
                              <label class="half" for="rating3" title="1 1/2 stars" style="color: #F09B0A;"></label>
                              <label for="rating2" title="1 star" style="color: #F09B0A;"></label>
                              <label class="half" for="rating1" title="1/2 star" style="color: #F09B0A;"></label>
                      </div>
                    </div>
                    
                    </div>
                    <!----Comments-------------------------------------->
                    <div class="card-body">
                      <p class="card-text">
                        迪欣湖位於香港大嶼山的竹篙灣，是迪士尼樂園邊上的新發現，走法也一樣，港鐵--欣奧--迪士尼，
                        然後步行15分鐘即可到達。走進園裡，到處都是別樣的樹、雅緻的花，不多的休閑人在觀靜靜的湖水。
                        迪欣湖是人工湖，佔地約30公頃，園內有一個面積達12公頃的人造蓄水池、緩跑徑、兒童遊樂場、服務中心和植物園。
                        迪欣湖用作公共水上康樂中心，以及為迪士尼樂園提供灌溉用水。
                      </p>
                    </div>
                  </div>
            <! -- Comment Example 2 End-->
            
            
            
            
            </div>
        </div>
    </div>
    <!-- main 4 End-->
    
    
    <!-- JQuery -->
    <script>
        require.config({
            paths:{
                activity_details: ['myself/page/activity_details'],
            },
        });
        loadModules(['activity_details'])
    </script>
    
    body;

    }

    /**
     * @inheritDoc
     */
    function post(array $data): array {
        return array();
    }

    /**
     * @inheritDoc
     */
    function path(): string {
        return '<a href="https://fyp.cocomine.cc/">首頁</a> > 迪欣湖';
    }

    /**
     * @inheritDoc
     */
    public function get_Title(): string {
        return '迪欣湖';
    }

    /**
     * @inheritDoc
     */
    public function get_Head(): string {
        return '迪欣湖';
    }
}