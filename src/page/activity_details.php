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
        // Event data
        $stmt = $this->sqlcon->prepare("SELECT summary, precautions_html, description_html, location, country, region, latitude, longitude, post_time, create_time FROM Event WHERE ID = ?");
        $stmt->bind_param("s", $this->UpPath[0]);
        if(!$stmt->execute()){
            echo_error(500);
            exit;
        }
        $event_data = $stmt->get_result()->fetch_assoc();

        //event image
        $stmt->prepare("SELECT media_ID, `order` FROM Event_img WHERE event_ID = ? ORDER BY `order`");
        $stmt->bind_param("s", $this->UpPath[0]);
        if(!$stmt->execute()){
            echo_error(500);
            exit;
        }
        $event_data['img'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        //event review
        $stmt->prepare("SELECT r.* FROM Book_review r, Book_event b WHERE r.Book_ID = b.ID AND event_ID = ?");
        $stmt->bind_param("s", $this->UpPath[0]);
        if(!$stmt->execute()){
            echo_error(500);
            exit;
        }
        $book_review = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        //event review img
        $event_data['review'] =array_map(function($review){
            $stmt = $this->sqlcon->prepare("SELECT media_ID FROM Book_review_img WHERE Book_review_ID = ?");
            $stmt->bind_param("s", $review['ID']);
            if(!$stmt->execute()){
                echo_error(500);
                exit;
            }
            $review['img'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            return $review;
        }, $book_review);


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
                    <p class="card-text">{$event_data['summary']}</p>
                </div>
            </div>
        </div>
        <!-- 注意事項 -->
        <div class="col-12">
            <div class="card bg-danger bg-opacity-10">
                <div class="card-body">
                    <h3 class="card-title">注意事項</h3>
                    <div class="card-text">{$event_data['precautions_html']}</div>
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
                    <div class="card-text">{$event_data['description_html']}</div>
                </div>
            </div>
        </div>
body . <<<body
        <!-- 地點 -->
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title" id="map_title">地點</h3>
                    <p class="card-text" id="map_address">{$event_data['location']}</p>
                    <div id="map" style="width:100%;height:10rem;"></div>
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