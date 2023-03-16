<?php

namespace page;

use cocomine\IPage;
use mysqli;

class activity_details implements IPage {

    private array $UpPath;
    private string $activity_name;
    private mysqli $sqlcon;

    function __construct(mysqli $sqlcon, array $UpPath) {
        $this->sqlcon = $sqlcon;
        $this->UpPath = $UpPath;
    }

    /**
     * @inheritDoc
     */
    public function access(bool $isAuth, int $role, bool $isPost): int {
        $stmt = $this->sqlcon->prepare("SELECT name FROM Event WHERE ID = ?");
        $stmt->bind_param("s", $this->UpPath[0]);
        if(!$stmt->execute()) return 500;

        $result = $stmt->get_result();
        if($result->num_rows == 0) return 404;

        $this->activity_name = $result->fetch_assoc()["name"];
        return 200;
    }

    /**
     * @inheritDoc
     */
    public function showPage(): string {
        //todo
        return <<<body
<div class="container mt-4">
    <div class="row gy-4">
        <!-- 活動圖片 -->
        <div class="col-12">
            <div class="card">
                <div style="width:100%; float:right; " class="">
                    <div id="carouselExampleIndicators" class="carousel slide" data-bs-ride="true" style="padding: 20px">
                        <div class="carousel-indicators">
                            <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
                            <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="1" aria-label="Slide 2"></button>
                            <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="2" aria-label="Slide 3"></button>
                        </div>
                        <div class="carousel-inner">
                            <div class="carousel-item active">
                                <img src="https://fyp.cocomine.cc/panel/api/media/jY4Rkm" class="d-block w-100" alt="" style="max-height:50vh;">
                            </div>
                            <div class="carousel-item">
                                <img src="https://fyp.cocomine.cc/panel/api/media/jY4Rkm" class="d-block w-100" alt="" style="max-height:50vh;">
                            </div>
                            <div class="carousel-item">
                                <img src="https://fyp.cocomine.cc/panel/api/media/jY4Rkm" class="d-block w-100" alt="" style="max-height:50vh;">
                            </div>
                        </div>
            
                        <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Previous</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Next</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <!-- 活動摘要 -->
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title">活動摘要</h3>
                    <p class="card-text">
                        無論是在湖邊散步、慢跑，還是騎著腳踏遊覽車在湖邊遊覽，皆可飽覽迪欣湖恬靜優美的湖面風光，
                        最適合一家大小一同來呼吸新鮮空氣，好好舒展身心。這個名為迪欣湖的人工湖有著波光粼粼的湖水、
                        動感十足的噴泉和優美怡人的山景，絕對是你與摯愛重拾昔日樂趣、盡情玩樂的好地方。
                    </p>
                </div>
            </div>
        </div>
        <!-- 注意事項 -->
        <div class="col-12">
            <div class="card bg-danger bg-opacity-10">
                <div class="card-body">
                    <h3 class="card-title">注意事項</h3>
                    <div class="card-text">
                        <ul style="list-style-type:disc;">
                            <li>活動時間：2021/06/01 09:00 - 2021/06/01 17:00</li>
                            <li>活動地點：台北市中山區中山北路二段</li>
                            <li>活動費用：$150</li>
                            <li>活動人數：1~10人</li>
                            <li>活動語言：中文</li>
                            <li>活動須知：請務必準時參加活動，如有遲到，請提前聯絡我們</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <!-- 預訂活動 -->
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title">預訂活動</h3>
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
                    <button type="button" class="btn btn-primary btn-lg" style="width:15vh; float:right;margin-right: 1vh;margin-bottom: 1vh" id="order">立刻預訂</button>
                </div>
            </div>
        </div>
        <!-- 活動詳情 -->
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title">活動詳情</h3>
                    <div class="card-text">
                        迪欣湖活動中心，簡稱迪欣湖，鄰近香港廸士尼樂園，在迪欣湖，既可欣賞大自然優美景色，
                        同時亦可享受活動中心提供的各項活動服務及配套，的確是香港少有遠離繁囂的休閒地點。
                        周末不少人到迪欣湖影相打卡，除了model外，還有很多是拍婚紗照的新人。近來仲有不少人在迪欣湖這個背山面湖的草地上享野餐樂添！
                    </div>
                </div>
            </div>
        </div>
body . <<<body
        <!-- 地點 -->
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title" id="map_title">地點</p>
                    <img src="/assets/images/icon/Logo-big.png" class="d-block w-100" width="500" height="800" style="padding: 30px;">
                </div>
            </div>
        </div>
    
        <!-- 評價 -->
        <div class="col-12">
            <div class="card">
                <div class="card-body">  
                    <h3 class="card-title">評價</h3>
                    
                </div>
            </div>
        </div>
        
    </div>
</div>
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
        return '<li class="breadcrumb-item"><a href="/">'.showText("index.home").'</a></li>'
            .'<li class="breadcrumb-item active">'.$this->activity_name.'</li>';
    }

    /**
     * @inheritDoc
     */
    public function get_Title(): string {
        return $this->activity_name.' | X-Travel';
    }

    /**
     * @inheritDoc
     */
    public function get_Head(): string {
        return $this->activity_name;
    }
}