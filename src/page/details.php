<?php

namespace page;

use cocomine\IPage;
use mysqli;

class details implements IPage {

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
        $stmt = $this->sqlcon->prepare("SELECT name FROM Event WHERE ID = ? AND (state = 1 OR state = 2)");
        $stmt->bind_param("s", $this->UpPath[0]);
        if (!$stmt->execute()) return 500;

        $result = $stmt->get_result();
        if ($result->num_rows == 0) return 404;

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
        if (!$stmt->execute()) {
            echo_error(500);
            exit;
        }
        $event_data = $stmt->get_result()->fetch_assoc();
        $event_data['country'] = self::$country[$event_data['country']]; //轉換國家代碼為中文
        $event_data['map-location'] = json_encode(array('lat' => $event_data['latitude'], 'lng' => $event_data['longitude'])); //將經緯度轉換為json

        /* event image */
        $stmt->prepare("SELECT media_ID FROM Event_img WHERE event_ID = ? ORDER BY `order`");
        $stmt->bind_param("s", $this->UpPath[0]);
        if (!$stmt->execute()) {
            echo_error(500);
            exit;
        }
        $event_img = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        # 將array轉換為img html, 並將第一個圖片設為active
        $event_data['img'] = join("", array_map(function ($img, $index) {
            if ($index == 0) $active = "active";
            return
                "<div class='carousel-item {$active}'>
                    <div class='ratio ratio-21x9'>
                        <img src='/panel/assets/images/image_loading.webp' data-src='/panel/api/media/{$img['media_ID']}' class='d-block w-100 lazy head-image' draggable='false' alt='{$img['media_ID']} Image'>
                    </div>
                </div>";
        }, $event_img, array_keys($event_img)));

        # 將array轉換為button html, 並將第一個button設為active
        $event_data['img_btn'] = join("", array_map(function ($img, $index) {
            if ($index == 0) {
                return "<button type='button' data-bs-target='#carousel' data-bs-slide-to='$index' class='active' aria-current='true' aria-label='Slide $index'></button>";
            } else {
                return "<button type='button' data-bs-target='#carousel' data-bs-slide-to='$index' aria-label='Slide $index'></button>";
            }
        }, $event_img, array_keys($event_img)));


        /* event review */
        $stmt->prepare("SELECT r.*, u.Email, u.Name FROM Book_review r, Book_event b, User u WHERE r.Book_ID = b.ID AND b.User = u.UUID AND event_ID = ?");
        $stmt->bind_param("s", $this->UpPath[0]);
        if (!$stmt->execute()) {
            echo_error(500);
            exit;
        }
        $book_reviews = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        # 取得評論圖片
        $stmt->prepare("SELECT media_ID FROM Book_review_img WHERE Book_review_ID = ?");
        $book_reviews = array_map(function ($review) use ($stmt) {
            $stmt->bind_param("s", $review['Book_ID']);
            if (!$stmt->execute()) {
                echo_error(500);
                exit;
            }
            $review['img'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

            // 取得預約方案
            $stmt->prepare("SELECT p.plan_name FROM Book_event_plan b, Event_schedule e, Event_plan p WHERE b.event_schedule = e.Schedule_ID AND e.plan = p.plan_ID AND b.Book_ID = ?");
            $stmt->bind_param("s", $review['Book_ID']);
            if (!$stmt->execute()) {
                echo_error(500);
                exit;
            }
            $review['plan'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

            return $review;
        }, $book_reviews);

        # 將array轉換為review html
        $event_data['review'] = join("", array_map(function ($review) {
            $avatar = md5($review['Email']); //將email轉換為md5
            $post_date = date("Y/m/d", strtotime($review['DateTime'])); //將日期轉換為Y/m/d格式
            $images = join("", array_map(function ($img) {
                return
                    "<div class='col-6 col-sm-4 col-md-3 col-lg-2 col-xxl-1'>
                        <div class='ratio ratio-1x1 rounded overflow-hidden'>
                            <img src='/panel/assets/images/image_loading.webp' data-src='/panel/api/media/{$img['media_ID']}' class='d-block w-100 lazy review-image' draggable='false' alt='{$img['media_ID']} Image'>
                        </div>
                    </div>";
            }, $review['img'])); //將圖片轉換為img html
            $rate = join("", array_fill(0, $review['rate'], "<i class='fa-solid fa-star text-warning'></i>")); //將評分轉換為星星html
            $rate .= join("", array_fill(0, 5 - $review['rate'], "<i class='fa-regular fa-star text-muted'></i>")); //將評分轉換為星星html
            $plan = join("", array_map(function ($plan) {
                return "<span class='status-p bg-primary bg-opacity-75 text-center me-2 plan-name'>{$plan['plan_name']}</span>";
            }, $review['plan'])); //將預約方案轉換為badge html

            return <<<review
<div class="col-12">
    <div class="card">
        <div class="card-header">
            <div class="rounded-circle overflow-hidden float-start me-2" style="max-width: 60px; height: auto">
                <img src="https://www.gravatar.com/avatar/$avatar?s=200" alt="avatar">
            </div>
            <h5><b>{$review['Name']}</b></h5>
            <div>$rate</div>
            <span class="text-muted">$post_date</span>
            <p>$plan</p>
        </div>
        <div class="card-body">
            <p class="card-text">{$review['comment']}</p>
            <div class="row g-2">$images</div>
            <div class="mt-2 w-100 ratio ratio-21x9 zoom-image bg-secondary bg-opacity-50 rounded overflow-hidden" style="display: none"></div>
        </div>
    </div>
</div>
review;
        }, $book_reviews));
        $event_data['review'] = $event_data['review'] == "" ? "<div class='col-12'><h5 class='text-center'>尚無評論</h5></div>" : $event_data['review']; //若沒有評論則顯示尚無評論

        # 計算評論總數及平均評分
        $event_data['review_total'] = count($book_reviews); //取得評論總數
        if($event_data['avg_rate'] > 0){
            $event_data['avg_rate'] = round(array_sum(array_column($book_reviews, 'rate')) / $event_data['review_total'], 1); //取得平均評分
            $event_data['rate_start'] = join("", array_fill(0, floor($event_data['avg_rate']), "<i class='fa-solid fa-star text-warning'></i>")); //將平均評分轉換為星星html
            if ($event_data['avg_rate'] - floor($event_data['avg_rate']) != 0)
                $event_data['rate_start'] .= "<i class='fa-solid fa-star-half-alt text-warning'></i>"; //將平均評分轉換為星星html (0.5分)
            $event_data['rate_start'] .= join("", array_fill(0, 5 - ceil($event_data['avg_rate']), "<i class='fa-regular fa-star text-muted'></i>")); //將平均評分轉換為星星html
        }else{
            $event_data['avg_rate'] = '-';
            $event_data['rate_start'] = join(array_fill(0, 5, "<i class='fa-regular fa-star text-muted'></i>")); //將平均評分轉換為星星html (0分
        }

        return <<<body
<link href='https://api.mapbox.com/mapbox-gl-js/v2.12.0/mapbox-gl.css' rel='stylesheet' />
<link rel="stylesheet" href="/panel/assets/css/myself/datetimepicker.css">
<style>
.head-image {
    object-fit: contain;
}
.review-image {
    object-fit: cover;
    cursor: zoom-in;
}
.plan-name{
    font-size: 0.3em;
}
#event-detail img {
    border-radius: 0.5rem;
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
                    <div class="row align-items-center mb-1">
                        <img src="/assets/images/icon/megaphone.png" alt="" class="p-2 col-auto ms-3" style="width: 52px; background-color: var(--bs-light); border-radius: 50%"/> 
                        <h3 class="card-title col-auto m-0">注意事項</h3>
                    </div>
                    <div class="card-text">{$event_data['precautions_html']}</div>
                </div>
            </div>
        </div>
        <!-- 預訂活動 -->
        <div class="col-12">
            <div class="card" style="background-color: #efefef">
                <div class="card-body">
                    <h3 class="card-title">預訂活動</h3>
                    <div class="date-picker date-picker-inline mt-4" id="book-date">
                       <label for="book-date" class="form-label">預約日期</label><br>
                       <input type="date" class="date-picker-toggle form-control form-rounded w-auto">
                       <div class="invalid-feedback">請選擇正確可預約日期</div>
                       <div class="date-calendar bg-light rounded p-1 d-inline-block mt-1 border border-1"></div>
                    </div>
                    <div class="mt-4">
                        <label class="form-label">可預訂方案時段</label>
                        <div id="plan" class="row gy-2 mx-1"></div>
                    </div>
                    <div class="row justify-content-between mt-4 p-1">
                        <h4 class="col-auto ali" id="total">$ 0</h4>
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
                    <div class="card-text" id="event-detail">{$event_data['description_html']}</div>
                </div>
            </div>
        </div>
body. <<<body
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
                    <h3 class="card-title">評論</h3>
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <span class="fs-2">{$event_data['avg_rate']}</span><span class="fs-6">/5.0</span>
                            </div>
                            <div class="col-auto fs-5">{$event_data['rate_start']}</div>
                            <span class="text-muted col">{$event_data['review_total']}則評論</span>
                        </div>
                        <div class="row gy-2" id="review">{$event_data['review']}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" tabindex="-1" id="confirm">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">發現衝突時段</h5>
      </div>
      <div class="modal-body">
        <p>我們發現了相同時段, 有其他活動預定</p>
        <ul style="list-style: disc"></ul>
        <p>你要繼續預定嗎?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-rounded" data-bs-dismiss="modal"><i class="fa-solid fa-xmark me-2"></i>取消</button>
        <button type="button" class="btn btn-primary btn-rounded" id="confirm-checkout"><i class="fa-solid fa-arrow-right me-2"></i>繼續預定</button>
      </div>
    </div>
  </div>
</div>
<script>
    require.config({
        paths:{
            datepicker: ['myself/datepicker'],
            details: ['myself/page/details'],
            'mapbox-gl': ['https://api.mapbox.com/mapbox-gl-js/v2.12.1/mapbox-gl'],
        },
    });
    loadModules(['details', 'datepicker', 'mapbox-gl']);
</script>
body;
    }

    /**
     * @inheritDoc
     */
    function post(array $data): array {

        /* 尋找當月可用日期 */
        if ($_GET['type'] === "available_date") {
            # 尋找重複可用日期
            $stmt = $this->sqlcon->prepare("SELECT DISTINCT start_date, end_date, repeat_week FROM Event_schedule 
                                                  WHERE Event_ID = ? AND type = 1 
                                                    AND MONTH(?) BETWEEN MONTH(start_date) AND MONTH(end_date) 
                                                    AND YEAR(?) BETWEEN YEAR(start_date) AND YEAR(end_date) ORDER BY start_date");
            $stmt->bind_param("sss", $this->UpPath[0], $data['date'], $data['date']);
            if (!$stmt->execute()) {
                return array('code' => 500, 'Message' => $stmt->error, 'Title' => showText('Error_Page.something_happened'));
            }
            $repeat = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

            # 尋找單次可用日期
            $stmt->prepare("SELECT start_date FROM Event_schedule WHERE Event_ID = ? AND type = 0 AND MONTH(start_date) = MONTH(?) AND YEAR(start_date) = YEAR(?)");
            $stmt->bind_param("sss", $this->UpPath[0], $data['date'], $data['date']);
            if (!$stmt->execute()) {
                return array('code' => 500, 'Message' => $stmt->error, 'Title' => showText('Error_Page.something_happened'));
            }
            $single = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

            return array('code' => 200, 'data' => array('repeat' => $repeat, 'single' => $single));
        }

        /* 尋找當日可用時段 */
        if($_GET['type'] === "available_plan"){
            $weekday = '%'.date('w', strtotime($data['date'])).'%'; // 取得當日星期幾
            $stmt = $this->sqlcon->prepare(
                "SELECT s.Schedule_ID, p.plan_name, s.start_time, s.end_time, p.price, p.max_each_user, 
                         # 計算當日剩餘人數
                         CAST(p.max_people - IFNULL((
                             # 計算當日已預訂人數
                             SELECT SUM(bp.plan_people) FROM Book_event b, Book_event_plan bp 
                             # 尋找當日已預訂人數
                             WHERE b.ID = bp.Book_ID AND b.event_ID = s.Event_ID AND bp.event_schedule = s.Schedule_ID AND b.book_date = ?
                             GROUP BY bp.event_schedule
                         ), 0) AS INT) AS `max_people` 
                     FROM Event_schedule s, Event_plan p 
                     # 尋找當日可用時段
                     WHERE s.Event_ID = p.Event_ID AND s.plan = p.plan_ID AND s.Event_ID = ? AND (
                         # 尋找重複可用時段
                         (type = 1 AND ? BETWEEN s.start_date AND s.end_date AND repeat_week LIKE ?) OR 
                         # 尋找單次可用時段
                         (type = 0 AND start_date = ?)
                     ) ORDER BY s.start_time");
            $stmt->bind_param("sssss", $data['date'], $this->UpPath[0], $data['date'], $weekday, $data['date']);
            if (!$stmt->execute()) {
                return array('code' => 500, 'Message' => $stmt->error, 'Title' => showText('Error_Page.something_happened'));
            }

            $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            return array('code' => 200, 'data' => $result);
        }

        return array('code' => 404, 'Message' => 'Required data request type');
    }

    /**
     * @inheritDoc
     */
    function path(): string {
        return '<li class="breadcrumb-item"><a href="/">' . showText("index.home") . '</a></li>'
            . '<li class="breadcrumb-item active">' . $this->activity_name . '</li>';
    }

    /**
     * @inheritDoc
     */
    public function get_Title(): string {
        return $this->activity_name . ' | X-Sport';
    }

    /**
     * @inheritDoc
     */
    public function get_Head(): string {
        return $this->activity_name;
    }
}