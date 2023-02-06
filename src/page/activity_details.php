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
    <style>
        .center {
            padding: 10px;
            background-color: #FFFFFF;
            border-radius: 15px;
            /*width: 100vh;  */
        }
    
        .background {
            background-color: #F3F8FB;
        }
    
        .title {
            font-weight: bold;
            font-size: 25px;
            padding: 10px;
        }
    
        .wrapper {
            overflow: hidden;
            float: right;
            background-color: #FFFFFF;
        }
    
        .normal_text {
            font-size: 15px;
            padding: 10px;
        }
    
        .normal_text a {
            text-decoration: none;
            color: #06ABE5;
        }
    
    
        .Rating_Number {
            float: left;
            font-size: 35px;
            padding-left: 15px;
            font-weight: bold;
        }
    
        .Rating_Number_maximum {
            float: left;
            padding-right: 5px;
            color: #6B6B6B;
        }
    
        .button_Comment_Selection {
            margin-left: 10px;
        }
    
        .Price_txt {
            font-size: 3.5vh;
            font-weight: 700;
            margin-bottom: 1vh;
        }
    
        .Price_Sign {
            font-size: 3.5vh;
            font-weight: 700;
            margin-left: 3vh;
    
        }
    
        .Div_Price_activity_numb {
            background-color: #F3F8FB;
            padding: 3vh;
            margin: 4vh;
            height: 10vh;
            font-size: 3vh;
        }

        .Div_Plus_Minus {
            float: right;
        }
    
        .txt_Num {
            font-size: 2.5vh;
            padding: 2vh;
        }
    
    
        /* Ratings Setting <<<<*/
        /* Ratings widget */
        .rate {
            display: inline-block;
            border: 0;
            margin-top: -25px;
        }
    
        /* Hide radio */
        .rate>input {
            display: none;
        }
    
        /* Order correctly by floating highest to the right */
        .rate>label {
            float: right;
        }
    
        /* The star of the show */
        .rate>label:before {
            display: inline-block;
            font-size: 2rem;
            padding: .3rem .2rem;
            margin: 0;
            cursor: pointer;
            font: var(--fa-font-solid);
            content: '\\f005';
            /* full star */
        }
    
        /* Half star trick */
        .rate .half:before {
            content: '\\f089';
            /* half star no outline */
            position: absolute;
            padding-right: 0;
        }
        /* Ratings Setting End <<<<*/
        
        
        /*  Comment Style */
        
        
    .profile-img {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        overflow: hidden;
        margin-right: 10px;
    }

    .profile-img img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
    }
    
    .profile {
        display: flex;
        align-items: center;
    }

    
    .name-user {
        display: flex;
        flex-direction: column;
    }

    .name-user strong {
        color: #3d3d3d;
        font-size: 1.1rem;
        letter-spacing: 0.5px;
    }

    .name-user span {
        color: #979797;
        font-size: 0.8rem;
    }
    
    .reviews {
        margin-top: 10px;
        margin-left: 5vh;
    }
    
    .client-comment{
    }
    
     /*  Comment Style End */
    </style>
    <meta charset="utf-8">
    <!-- main 1 -->
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
        <!-- Carousel End-->
    
    </div>
    <!-- main 1 End -->
    
    <br />
    <!-- activity -->
    <div class="center">
        <p class="title">活動介紹</p>
        <div class="card-body">
        無論是在湖邊散步、慢跑，還是騎著腳踏遊覽車在湖邊遊覽，皆可飽覽迪欣湖恬靜優美的湖面風光，
        最適合一家大小一同來呼吸新鮮空氣，好好舒展身心。這個名為迪欣湖的人工湖有著波光粼粼的湖水、
        動感十足的噴泉和優美怡人的山景，絕對是你與摯愛重拾昔日樂趣、盡情玩樂的好地方。
        </div>
    </div>
    <!-- activity  End -->
    
    
    
    <!-- Noted -->
    
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
    <!-- Noted End -->
    
    <!-- Price -->
    <div class="center">
        <p class="title">預訂活動</p>
        <div class="Div_Price_activity_numb">

        
        
            <span style="font-size: 2vh"><b>計劃</b></span>
            <div class="Div_Plus_Minus">
                 <button class="btn btn-outline-secondary" id="ti-plus"><i class="fa fa-plus" aria-hidden="true"></i></button>
                <span class="txt_Num">1</span>
                <button class="btn btn-outline-secondary" id="ti-minus"><i class="fa fa-minus" aria-hidden="true"></i></button>
            </div>
        </div>
        <span class="Price_Sign">$</span><span class="Price_txt">150</span>
        <button type="button" class="btn btn-primary btn-lg"
            style="width:15vh; float:right;margin-right: 1vh;margin-bottom: 1vh" id="order">立刻預訂</button>
    </div>
    
    <!-- Price End-->
    
    <!-- Event Details -->
    <div class="center">
        <p class="title">活動詳情</p>
      <div class="card-body">
        迪欣湖活動中心，簡稱迪欣湖，鄰近香港廸士尼樂園，在迪欣湖，既可欣賞大自然優美景色，
        同時亦可享受活動中心提供的各項活動服務及配套，的確是香港少有遠離繁囂的休閒地點。
        周末不少人到迪欣湖影相打卡，除了model外，還有很多是拍婚紗照的新人。近來仲有不少人在迪欣湖這個背山面湖的草地上享野餐樂添！
      </div>
    </div>
    <!-- Event Details End -->
    
    
    <br />
    <!-- main 3 -->
    <div class="center">
        <p class="title" id="map_title">地圖位置</p>
        <img src="/assets/images/icon/Logo-big.png" class="d-block w-100" width="500" height="800" style="padding: 30px;">
    </div>
    <!-- main 3 End-->
    
    
    
    <br />
    <!-- main 4 -->
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
            <button type="button" class="btn btn-outline-secondary" style="margin-right: 10px;">全部</button>
            <button type="button" class="btn btn-outline-secondary" style="margin-right: 10px;">4.0+</button>
            <button type="button" class="btn btn-outline-secondary" style="margin-right: 10px;">3.0+</button>
            <button type="button" class="btn btn-outline-secondary" style="margin-right: 10px;">3.0></button>
    
    
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
    <div  class="profile">
        <div class="profile-img">
            <img src="https://fyp.cocomine.cc/panel/api/media/jY4Rkm" />
        </div>
        
        <div class="name-user">
            <strong>Touseeq Ijaz</strong>
            <span>@touseeqijazweb</span>
        </div>
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
            <div class="client-comment">
            <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Exercitationem,
             quaerat quis? Provident temporibus architecto asperiores nobis maiores nisi a.
              Quae doloribus ipsum aliquam tenetur voluptates incidunt blanditiis sed atque cumque.
              </p>
        </div>   
        
        
        
    <div class="card text-bg-light mb-3" style="max-width: 100rem;" >
      <div class="card-header">
      
        <div class="profile-img">
            <img src="https://fyp.cocomine.cc/panel/api/media/jY4Rkm" />
        </div>
        
        <div class="name-user">
            <strong>Touseeq Ijaz</strong>
            <span>@touseeqijazweb</span>
        </div>
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
      <div class="card-body">
        <h5 class="card-title">Light card title</h5>
        <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's content.</p>
      </div>
    </div>
    <! -- Comment End-->
    
    
    
    </div>
    <!-- main 4 End-->
    
    
    <script>
        $(document).ready(function () {
            let num;
            let total = $(".Price_txt").text();
            $("#ti-plus").click(function () {
                num = parseInt($(".txt_Num").text()) + 1;
                $(".txt_Num").text(num);

                $(".Price_txt").text(parseInt(total) * num);
            });

            $("#ti-minus").click(function () {
                if (parseInt($(".txt_Num").text()) > 1) {
                    num = parseInt($(".txt_Num").text()) - 1;
                    $(".txt_Num").text(num);

                    $(".Price_txt").text(parseInt(total) * num);
                }
            });
        });

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