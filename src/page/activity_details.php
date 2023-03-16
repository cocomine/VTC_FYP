<?php

namespace page;

use cocomine\IPage;
use mysqli;

class activity_details implements IPage {

    private array $UpPath;
    private string $activity_name;
    private mysqli $sqlcon;
    private static array $country = array(
        'HK' => '香港',
        'TW' => '台灣',
        'CN' => '中國大陸',
        'MO' => '澳門',
    );

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
        /* Event data */
        $stmt = $this->sqlcon->prepare("SELECT summary, precautions_html, description_html, location, country, region, latitude, longitude, post_time, create_time FROM Event WHERE ID = ?");
        $stmt->bind_param("s", $this->UpPath[0]);
        if(!$stmt->execute()){
            echo_error(500);
            exit;
        }
        $event_data = $stmt->get_result()->fetch_assoc();
        $event_data['country'] = self::$country[$event_data['country']]; //轉換國家代碼為中文
        $event_data['map-location'] = json_encode(array('lat' => $event_data['latitude'], 'lng' => $event_data['longitude'])); //將經緯度轉換為json

        /* event image */
        $stmt->prepare("SELECT media_ID FROM Event_img WHERE event_ID = ? ORDER BY `order`");
        $stmt->bind_param("s", $this->UpPath[0]);
        if(!$stmt->execute()){
            echo_error(500);
            exit;
        }
        $event_img = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        # 將array轉換為img html, 並將第一個圖片設為active
        $event_data['img'] = join("", array_map(function($img, $index){
            if($index == 0) $active = "active";
            return
                "<div class='carousel-item {$active}'>
                    <div class='ratio ratio-21x9'>
                        <img src='/panel/assets/images/image_loading.webp' data-src='/panel/api/media/{$img['media_ID']}' class='d-block w-100 lazy head-image' alt='{$img['media_ID']} Image'>
                    </div>
                </div>";
        }, $event_img, array_keys($event_img)));

        # 將array轉換為button html, 並將第一個button設為active
        $event_data['img_btn'] = join("", array_map(function($img, $index){
            if($index == 0) {
                return "<button type='button' data-bs-target='#carousel' data-bs-slide-to='$index' class='active' aria-current='true' aria-label='Slide $index'></button>";
            }else{
                return "<button type='button' data-bs-target='#carousel' data-bs-slide-to='$index' aria-label='Slide $index'></button>";
            }
        }, $event_img, array_keys($event_img)));


        /* event review */
        $stmt->prepare("SELECT r.* FROM Book_review r, Book_event b WHERE r.Book_ID = b.ID AND event_ID = ?");
        $stmt->bind_param("s", $this->UpPath[0]);
        if(!$stmt->execute()){
            echo_error(500);
            exit;
        }
        $book_review = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        # event review img
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
<link href='https://api.mapbox.com/mapbox-gl-js/v2.12.0/mapbox-gl.css' rel='stylesheet' />
<link rel="stylesheet" href="/panel/assets/css/myself/datetimepicker.css">
<style>
.head-image {
    object-fit: contain;
}
</style>
<div class="container mt-4">
    <div class="row gy-4">
        <!-- 活動圖片 -->
        <div class="col-12">
            <div class="card overflow-hidden">
                <div class="w-100 bg-secondary bg-opacity-50">
                    <div id="carousel" class="carousel slide" data-bs-ride="carousel">
                        <div class="carousel-indicators">{$event_data['img_btn']}</div>
                        <div class="carousel-inner">{$event_data['img']}</div>
                        <button class="carousel-control-prev" type="button" data-bs-target="#carousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">上一張</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#carousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">下一張</span>
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
                    <div class="row align-items-center">
                        <img src="/assets/images/icon/megaphone.png" alt="" class="p-2 col-auto" style="width: 52px; background-color: var(--bs-light); border-radius: 50%"/> 
                        <h3 class="card-title col-auto m-0">注意事項</h3>
                    </div>
                    <div class="card-text">{$event_data['precautions_html']}</div>
                </div>
            </div>
        </div>
        <!-- 預訂活動 -->
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title">預訂活動</h3>
                    <div class="date-picker date-picker mt-4">
                       <label for="book-date" class="form-label">預約日期</label><br>
                       <input type="date" class="date-picker-toggle form-control form-rounded w-auto" id="book-date">
                    </div>
                    <div class="mt-4">
                        <label class="form-label">可預訂方案時段</label>
                        <div id="plan" class="row gy-2"></div>
                    </div>
                    <div class="row justify-content-between mt-4 p-1">
                        <h4 class="col-auto ali" id="total">$ --</h4>
                        <button type="button" class="btn btn-primary btn-rounded col-auto" id="checkout"><i class="fa-solid fa-cart-shopping me-2"></i>立即預訂</button>
                    </div>
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
                    <h3 class="card-title" id="map_title">活動位置</h3>
                    <p class="card-text">
                        {$event_data['location']}<br>
                        {$event_data['country']}, {$event_data['region']}
                    </p>
                    <div id="map" class="w-100 rounded" style="min-height:15rem;"></div>
                    <pre class="d-none" id="map-location">{$event_data['map-location']}</pre>
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
            datepicker: ['myself/datepicker'],
            activity_details: ['myself/page/activity_details'],
            'mapbox-gl': ['https://api.mapbox.com/mapbox-gl-js/v2.12.1/mapbox-gl'],
        },
    });
    loadModules(['activity_details', 'datepicker', 'mapbox-gl'])
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