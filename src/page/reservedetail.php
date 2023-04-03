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
class reservedetail implements IPage {

    private static array $country = array(
        'HK' => '香港',
        'TW' => '台灣',
        'CN' => '中國大陸',
        'MO' => '澳門',
    );
    private array $upPath;
    private mysqli $sqlcon;
    private string $bookID;
    /**
     * @var mixed
     */
    private string $event_name;


    /**
     * home constructor.
     * sql連接
     * @param mysqli $sqlcon
     * @param array $upPath
     */
    function __construct(mysqli $sqlcon, array $upPath) {
        $this->sqlcon = $sqlcon;
        $this->upPath = $upPath;
        $this->bookID = $this->upPath[0];
    }


    /**
     * @inheritDoc
     */
    public function access(bool $isAuth, int $role, bool $isPost): int {
        global $auth;
        if (!$isAuth) return 401;

        if (sizeof($this->upPath) > 0 && preg_match("/[0-9]+/", $this->upPath[0])) {
            $stmt = $this->sqlcon->prepare("SELECT b.event_ID AS event_ID, e.name , b.book_date AS 'count' FROM Book_event b,Event e  WHERE b.ID = ? AND b.User = ?");
            $stmt->bind_param('ss', $this->bookID, $auth->userdata['UUID']); //測試用：
            if (!$stmt->execute()) return 500;

            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            if ($result->num_rows <= 0) return 403;
            $this->event_name = $row['name'];
            return 200;
        }
        return 404;
    }

    /* 輸出頁面 */
    function showPage(): string {
        /* 取得預約資料 */
        $stmt = $this->sqlcon->prepare("SELECT event_ID, pay_price, book_date, order_datetime, invoice_number, invoice_url FROM Book_event WHERE ID = ?");
        $stmt->bind_param("s", $this->upPath[0]);
        if (!$stmt->execute()) {
            echo_error(500);
            exit;
        }
        $book_data = $stmt->get_result()->fetch_assoc();
        $book_data['pay_price'] = number_format($book_data['pay_price'], 2);

        /* 取得預約計劃 */
        $stmt->prepare("SELECT p.plan_name, b.plan_people, p.price,e.start_time, e.end_time FROM Book_event_plan b, Event_schedule e, Event_plan p 
                WHERE b.event_schedule = e.Schedule_ID AND e.plan = p.plan_ID AND b.Book_ID = ? AND e.Event_ID = ? AND p.Event_ID = ?");
        $stmt->bind_param("sss", $this->upPath[0], $book_data['event_ID'], $book_data['event_ID']);//
        if (!$stmt->execute()) {
            echo_error(500);
            exit;
        }

        $result = $stmt->get_result();
        $bookPlan_html= ""; // 預約計劃html
        $total_people = 0; // 總人數
        $total_price = 0; // 總價格
        while ($row = $result->fetch_assoc()) {
            $total_price += $row['price'];
            $row['price'] = number_format($row['price'] * $row['plan_people'], 2);
            $total_people += $row['plan_people'];

            $bookPlan_html .=
                "<tr>
                    <td>{$row['plan_name']}</td>
                    <td>{$row['start_time']}<i class='fa-solid fa-angles-right mx-2'></i>{$row['end_time']}</td>
                    <td>{$row['plan_people']}</td>
                    <td>{$row['price']}</td>
                </tr>";
        }
        $total_price = number_format($total_price, 2);

        //* 取得預約活動資料 */
        $stmt->prepare("SELECT name, location, country, region FROM Event WHERE ID = ?");
        $stmt->bind_param("s", $book_data['event_ID']);
        if (!$stmt->execute()) {
            echo_error(500);
            exit;
        }
        $event_data = $stmt->get_result()->fetch_assoc();
        $event_data['country'] = self::$country[$event_data['country']]; // 國家轉換 e.x.(TW -> 台灣)

        /* 圖片上載多語言 */
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
                'drag' => $Text2['drag']
            )
        ));

        return <<<body
<link rel="stylesheet" href="/assets/css/myself/media-select.css">
<pre id="media-select-LangJson" class="d-none">$LangJson</pre>
<style>
#rate-star i{
    cursor: pointer;
}
.was-validated :invalid~#rate-star > i{
    color: var(--bs-danger) !important;
}
#review-img-preview img{
    object-fit: cover;
}
</style>
<div class='container mt-4'>
    <div class="row gy-4">
        <!-- 預約詳情 -->
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">預約詳情</h4>                   
                    <div class="card-text">
                        <div class="row w-100 fs-6">
                            <div class="col col-lg-4">活動名稱:</div>
                            <div class="col-12 col-sm"><a href="/details/{$book_data['event_ID']}">{$event_data['name']}</a></div>
                            <div class="w-100 py-2 py-sm-0"></div>
                            <div class="col col-lg-4">活動地點: </div>
                            <div class="col-12 col-sm">{$event_data['location']}<br>{$event_data['country']}, {$event_data['region']}</div>
                            <div class="w-100 py-2 py-sm-0"></div>
                            <div class="col col-lg-4">預約日期(年/月/日): </div>
                            <div class="col-12 col-sm">{$book_data['book_date']}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- 預約時段/詳情 -->
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">預約時段/詳情</h4>
                    <div class="col-12">
                        <div class="table-responsive">
                            <table class="table">
                                <thead class="table-primary text-light" style="--bs-table-bg: var(--primary-color)">
                                    <tr>
                                        <th scope="col">計劃</th>
                                        <th scope="col">時段</th>
                                        <th scope="col">人數</th>
                                        <th scope="col">價錢</th>
                                    </tr>
                                </thead>
                                <tbody>$bookPlan_html</tbody>
                                <tfoot>
                                    <tr class="fs-5 fw-bold">
                                        <td colspan="2">總計</td>
                                        <td>$total_people</td>
                                        <td>{$book_data['pay_price']}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>    
                </div>
            </div>
        </div>
        <!-- 帳單 -->
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="card-text">
                        <p class="fs-5">應付款項: <span class="float-end fw-bold">$ $total_price</span></p>
                        <p class="fs-5">已付款項: <span class="float-end fw-bold">$ {$book_data['pay_price']}</span></p>
                        <div class="pt-4">
                            <p class="text-muted">
                                下單時間: {$book_data['order_datetime']}</br>
                                帳單編號: <a href="{$book_data['invoice_url']}" target="_blank">{$book_data['invoice_number']}</a></br>
                                預約編號: #{$this->upPath[0]}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- 評論 -->
        <div class="col-12">
            <div class="card">
                <div class="card-body">  
                    <h4 class="card-title">評論</h4>
                    <form id="review" class="needs-validation" novalidate>
                        <div class="row">
                            <div class="col-12">
                                <label for="review-rate" class="m-0">評分</label>
                                <input type="number" class="d-none" id="review-rate" name="review-rate" min="1" max="5" required>
                                <div class="fs-4 test" id="rate-star">
                                    <i class='fa-regular fa-star text-muted' data-rate="1"></i>
                                    <i class='fa-regular fa-star text-muted' data-rate="2"></i>
                                    <i class='fa-regular fa-star text-muted' data-rate="3"></i>
                                    <i class='fa-regular fa-star text-muted' data-rate="4"></i>
                                    <i class='fa-regular fa-star text-muted' data-rate="5"></i>
                                </div>
                                <div class="invalid-feedback">為活動評個分吧~~</div>
                            </div>
                            <div class="col-12">
                                <label for="review-comment" class="form-label">評論</label>
                                <textarea class="form-control" id="review-comment" name="review-comment" rows="3" maxlength="100" required></textarea>
                                <span class="float-end text-secondary" id="review-comment-count" style="margin-top: -20px; margin-right: 10px">0/100</span>
                                <div class="invalid-feedback">這裏不能留空哦~~</div>
                            </div>
                            <div class="col-12">
                                <input type="text" class="d-none" id="review-img" name="review-img">
                                <button type="button" class="btn btn-rounded btn-primary mt-4 pr-4" id="review-img-sel"><i class="fa-regular fa-image me-2"></i>選擇圖片</button>
                                <div class="row gx-2 mt-3" id="review-img-preview"></div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-rounded btn-primary mt-4 pr-4 form-submit"><i class="fa-regular fa-paper-plane me-2"></i>送出</button>
                    </form>
                </div>
            </div>
        </div>  
    </div>    
</div>
<script>
require.config({
        paths:{
            'media-select': 'myself/media-select',
            'media-select.upload': 'myself/media-select.upload',
        }
    })
loadModules(['myself/page/reservedetail', 'media-select', 'media-select.upload']);
</script>
body;
    }

    /* POST請求 */
    function post(array $data): array {
        $stmt = $this->sqlcon->prepare("INSERT INTO Book_review (book_id, rate, comment) VALUES (?, ?, ?)");
        $stmt->bind_param("sis", $this->upPath[0], $data['review-rate'], $data['review-comment']);
        if(!$stmt->execute()) {
            return array(
                'code' => 500,
                'Title' => '發生錯誤',
                'Message' => $stmt->error
            );
        }

        if($stmt->affected_rows == 0) {
            return array(
                'code' => 500,
                'Title' => '發生錯誤',
                'Message' => '無法新增評論'
            );
        }else{
            return array(
                'code' => 200,
                'Title' => '新增成功',
                'Message' => '感謝您的評論'
            );
        }
    }

    /* path輸出 */
    function path(): string {
        return '<li class="breadcrumb-item active"><a href="/">' . showText("index.home") . '<a></li>'
            . '<li class="breadcrumb-item active"><a href="/reserve_view">' . '預訂管理' . '<a></li>'
            . '<li class="breadcrumb-item active">預約詳情 #' . $this->bookID . '</li>';
    }

    /* 取得頁面標題 */
    public function get_Title(): string {
        return '預約詳情 | X-Travel';
    }

    /* 取得頁首標題 */

    public function get_Head(): string {
        return $this->event_name.' 預約詳情';
    }

}