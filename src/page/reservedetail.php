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
    private array $UpPath;
    private string $activity_name;
    private mysqli $sqlcon;


    /**
     * home constructor.
     * sql連接
     * @param $sqlcon
     */
    function __construct(mysqli $sqlcon, array $upPath) {
        $this->sqlcon = $sqlcon;
        $this->upPath = $upPath;
    }


    /* 是否有權進入 */

    public function access(bool $isAuth, int $role, bool $isPost): int {
        global $auth;
        $this->bookID = $this->upPath[0];


        if (!$isAuth) return 401;

        if (sizeof($this->upPath) > 0 && preg_match("/[0-9]+/", $this->upPath[0])) {
            $stmt = $this->sqlcon->prepare("SELECT b.event_ID,e.name ,b.book_date, COUNT(*) as 'count' FROM Book_event as b,Event as e  WHERE b.ID = ? AND b.User = '8be832fd-af63-11ed-9cd6-0011329060ef'");
            $stmt->bind_param('s', $this->bookID); //測試用：, $auth->userdata['UUID']
                if (!$stmt->execute()) return 500;

                $result = $stmt->get_result();
                $row = $result->fetch_assoc();

                if ($row['count'] <= 0) return 403; //403
                $this->event_name = $row['e.name'];
                $this->book_date = $row['b.book_date'];
                $this->event_ID = $row['b.event_ID'];
                print_r($this->event_ID);

            }

            return 200; //代碼問題：無法正確輸出eventID導致下方無法正確運行
        }

    /* 輸出頁面 */
    function showPage(): string {
        $this->event_ID='52';//測試用
        $Text = showText('index.home');

        /* json 語言 */
        $jsonLang = json_encode(array());

        global $auth; //獲取全域變數 $auth, class: cocomine/MyAuth
        $userdata = $auth->userdata; //獲取用戶資訊
        $stmt = $this->sqlcon->prepare("SELECT name, summary, precautions_html, description_html, location, country, region, latitude, longitude, post_time, create_time FROM Event WHERE ID = ?");
        $stmt->bind_param("s", $this->event_ID);
        if (!$stmt->execute()) {
            echo_error(500);
            exit;
        }
        $event_data = $stmt->get_result()->fetch_assoc();

        global $auth; //獲取全域變數 $auth, class: cocomine/MyAuth
        $userdata = $auth->userdata; //獲取用戶資訊
        $stmt = $this->sqlcon->prepare("SELECT e.ID, e.User, e.event_ID, e.pay_price, e.book_date as date,e.order_datetime as datetime,p.event_schedule,p.plan_people FROM Book_event as e,Book_event_plan as p WHERE e.ID = ?");
        $stmt->bind_param("s", $this->bookID);
        if (!$stmt->execute()) {
            echo_error(500);
            exit;
        }
        $book_data = $stmt->get_result()->fetch_assoc();





        return <<<body
<pre id='langJson' style='display: none'>$jsonLang</pre>
<style>
#Background {
    background-image: url('/assets/images/background/hot-air-balloon-back.jpg');
    height: 100vh; 
    background-repeat: no-repeat; 
    background-position: center; 
    background-size: cover;
}

#Background:before {
    content: '';
    background-color: black;
    opacity: 0.3;
    position: absolute;
    top: 0;
    left: 0;
    bottom: 0;
    right: 0;
    z-index: -1;
}

.card-img-top {
    width: 300px;
    height: 300px;
    clip: rect(10px,290px,290px,10px);
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}
.form-head {
    text-align: center;
    background: -webkit-linear-gradient(left, #ff7112 0%, #ff9e5b 100%);
    background: linear-gradient(to right, #ff7112 0%, #ff9e5b 100%);
    padding: 50px;
}

.form-head h4{
    letter-spacing: 0;
    text-transform: uppercase;
    font-weight: 600;
    margin-bottom: 7px;
    color: #fff;
}
.form-head h6{
    letter-spacing: 0;
    text-transform: uppercase;
    font-weight: 600;
    margin-bottom: 7px;
    color: #fff;
}
.form-head p {
    color: #fff;
    font-size: 14px;
    line-height: 22px;
}
.
</style>

<div class='container mt-4'>
    <div class="row gy-4">

            
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title">預約详细</h3>                   
                    <div>
                        <div style="float:left"><p class="text-secondary">活動名字： </div>
                        <div style="float:right"><span id="event_name">{$event_data['name']}</span></div></p><br>
                    </div>
                    <div style="clear:both"></div>
                    <div>
                        <div style="float:left"><p class="text-secondary">活動地址：</div>
                        <div style="float:right"><span id="location">{$event_data['location']}</span></div></p><br>
                    </div>
                     <div style="clear:both"></div>
                    <div>
                        <div style="float:left"><p class="text-secondary">預約日期：</div>
                        <div style="float:right"><span id="date">{$book_data['date']}</span></div></p><br>
                    </div>
                    <div style="clear:both"></div>
                </div>
            </div>
        </div>
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title">預約计划/預約時段/預約詳情</h3>
                    <div class="col-12">
                        <div class="table-responsive">
                            <table class="table">
                                <thead class="table-primary text-light" style="--bs-table-bg: var(--primary-color)">
                                    <tr>
                                        <th scope="col">活動計劃</th>
                                        <th scope="col">活動開始結束時段</th>
                                        <th scope="col">預約人數</th>
                                        <th scope="col">預約價錢</th>
                                    </tr>
                                </thead>
                                    <tr>
                                        <th scope="col">{$event_data['name']}</th>
                                        <th scope="col">12:00:00-18:00:00</th> //時間改！！！
                                        <th scope="col">1</th>
                                        <th scope="col">{$book_data['pay_price']}</th>
                                    </tr>
                                <tbody id="reserve_detail"></tbody>
                                <tfoot>
                                    <tr>
                                        <th scope="row">總計</th>
                                        <th scope="col"></th>
                                        <th scope="col">總人數</th>
                                        <th scope="col">總價錢</th>
                                    </tr>
                                
                                <tr>
                                    <th scope="col"></th>
                                    <th scope="col"></th>
                                    <th scope="col">1</th>
                                    <th scope="col">{$book_data['pay_price']}</th>
                                </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>    
                    <p class="card-text"></p>
                </div>
            </div>
        </div>
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <p class="card-text"></p>
                    <div>
                         <div style="float:left"><h3 class="card-title">活動人數: </div>
                         <div style="float:right"><span id="plan_people">1</span></div>
                    </div>
                    <div style="clear:both"></div><br>
                    <div>
                         <div style="float:left"><h3 class="card-title">實際付款:</div>
                         <div style="float:right"><span id="order_time"><h5>HKD{$book_data['pay_price']}</h5></span></div>
                    </div>
                    <div style="clear:both"></div>
                    <hr>
                <div class="col-12">
                        
                     <div style="float:right"><p>下單時間: <span id="order_time">{$book_data['datetime']}</p><div>
                     <div style="clear:both"></div>
                     <div style="float:right"><p class="text-secondary">預約編號: # <span id="order_id">{$this->bookID}</span></p></div>
                        </div>
                </div>
                
                   

            </div>
        </div>
    </div>    
</div>

body. <<<body
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
      <!-- 載入外部JS -->
          require.config({
            paths:{
                zxcvbn: ['https://cdnjs.cloudflare.com/ajax/libs/zxcvbn/4.4.2/zxcvbn'],
                forge: ['https://cdn.jsdelivr.net/npm/node-forge/dist/forge.min'],
                FileSaver: ['FileSaver.min'],
            },
          });

loadModules(['myself/datepicker', 'myself/page/ho me'])
loadModules(['myself/page/ChangeSetting', 'zxcvbn', 'forge', 'FileSaver'])
</script>
body;
    }

    /* POST請求 */
    function post(array $data): array {
        global $auth;
        $output = array();
        $uuid = '8be832fd-af63-11ed-9cd6-0011329060ef'; //測試中: $auth-> userdata['UUID'];

        /* 取得該用戶建立的活動給參與人數 */
        /* */
        $stmt = $this->sqlcon->prepare("SELECT b.ID AS 'BookID', e.ID AS 'EventID', e.thumbnail, e.summary, e.name, b.pay_price, b.book_date
            FROM Book_event b, Event e WHERE e.ID = b.event_ID AND b.User = ?");
        $stmt->bind_param('s', $uuid);
        if (!$stmt->execute()) {
            return array(
                'code' => 500,
                'Title' => 'Database Error!',
                'Message' => $stmt->error,
            );
        }

        /* 展示用戶預約計劃 */
        $output = array();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $tmp = array(
                'ID' => $row['ID'],
                'Name' => $row['Name'],
                'full_name' => $row['last_name'].' '.$row['first_name'],
                'book_date' => $row['book_date'],
                'plan' => array(),
            );

            $stmt->prepare("SELECT p.plan_name, b.plan_people FROM Book_event_plan b, Event_schedule e, Event_plan p 
                                    WHERE b.event_schedule = e.Schedule_ID AND e.plan = p.plan_ID AND b.Book_ID = ? AND e.Event_ID = ? AND p.Event_ID = ?");
            $stmt->bind_param('sss', $row['ID'], $this->upPath[0], $this->upPath[0]);
            if (!$stmt->execute()) {
                return array(
                    'code' => 500,
                    'Title' => showText('Error_Page.some_thing_happen'),
                    'Message' => $stmt->error,
                );
            }

            $plan_result = $stmt->get_result();
            while ($row = $plan_result->fetch_assoc()) {
                $tmp['plan'][] = array(
                    'plan_name' => $row['plan_name'],
                    'plan_people' => $row['plan_people'],
                );
            }

            $output[] = $tmp;
        }
        return array('data' => $output);
    }

    /* path輸出 */
    function path(): string {
        return '<li class="breadcrumb-item active"><a href="/">' . showText("index.home") . '<a></li>'
            . '<li class="breadcrumb-item active">' . '预约订单#' . $this->bookID . '</li>';
    }

    /* 取得頁面標題 */
    public function get_Title(): string {
        return showText('預約詳情');
    }

    /* 取得頁首標題 */

    public function get_Head(): string {
        return showText("預約詳情");
    }

}