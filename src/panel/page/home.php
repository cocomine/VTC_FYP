<?php
/**
 * Copyright (c) 2020.
 * Create by cocomine
 */

namespace panel\page;

use cocomine\IPage;
use Moment\Moment;
use Moment\MomentException;
use mysqli;
use panel\apis\media;

/**
 * Class home
 * @package cocopixelmc\Page
 */
class home implements IPage {
    private mysqli $sqlcon;
    private int $role;

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
        $this->role = $role;
        if (!$isAuth) return 401;
        if ($role < 1) return 403;
        return 200;
    }

    /* 輸出頁面 */
    function showPage(): string {

        $Text = showText('index.Content');

        if ($this->role < 2) {
            return "<script>location.replace('/')</script>";
        } else {
            return <<<body
<style>
.today-order-list{
    height: 20rem;
}
</style>
<div class="col-md-6 col-lg-3">
    <div class="card">
        <div class="seo-fact sbg1">
            <div class="p-4 d-flex justify-content-between align-items-center">
                <div class="seofct-icon"><i class="fa-solid fa-coins"></i>本年賺取</div>
                <h2>$ <span id="year-earned">--</span></h2>
            </div>
            <canvas id="year-earned-chart" height="50"></canvas>
        </div>
    </div>
</div>
<div class="col-md-6 col-lg-3">
    <div class="card">
        <div class="seo-fact sbg2">
            <div class="p-4 d-flex justify-content-between align-items-center">
                <div class="seofct-icon"><i class="fa-solid fa-receipt"></i>本年新單</div>
                <h2 id="year-order">--</h2>
            </div>
            <canvas id="year-order-chart" height="50"></canvas>
        </div>
    </div>
</div>
<div class="col-md-6 col-lg-3">
    <div class="card">
        <div class="seo-fact sbg3">
            <div class="p-4 d-flex justify-content-between align-items-center">
                <div class="seofct-icon"><i class="fa-solid fa-coins"></i>本月賺取</div>
                <h2>$ <span id="month-earned">--</span></h2>
            </div>
            <canvas id="month-earned-chart" height="50"></canvas>
        </div>
    </div>
</div>
<div class="col-md-6 col-lg-3">
    <div class="card">
        <div class="seo-fact sbg4">
            <div class="p-4 d-flex justify-content-between align-items-center">
                <div class="seofct-icon"><i class="fa-solid fa-receipt"></i>本月新單</div>
                <h2 id="month-order">--</h2>
            </div>
            <canvas id="month-order-chart" height="50"></canvas>
        </div>
    </div>
</div>
body . <<<body
<div class="col-lg-8">
    <div class="card">
        <div class="card-body">
            <h4 class="card-title">今天預約</h4>
            <div class="scrollbar-dynamic today-order-list">
                <table class="table table-striped">
                    <thead class="table-primary text-light sticky-top" style="--bs-table-bg: var(--primary-color)">
                        <tr>
                            <th>#</th>
                            <th>客戶</th>
                            <th>活動計劃 / 預約人數</th>
                            <th>預約時間</th>
                        </tr>
                    </thead>
                    <tbody id="today-order">
                        <tr><td colspan="4" class="text-center">
                            <div id="pre-submit-load" style="height: 20px; margin-top: -4px"> <div class="submit-load"><div></div><div></div><div></div><div></div></div> </div>
                        </td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<div class="col-lg-4">
    <div class="card">
        <div class="card-body">
            <h4 class="card-title float-start">客戶國家/地區</h4>
            <p class="text-end"><small class="text-muted">(過去6個月)</small></p>
            <div>
                <div class="row justify-content-center w-100 g-0">
                    <div style="max-height: 350px" class="col-auto">
                        <canvas id="country" height="233"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="col-lg-7">
    <div class="card">
        <div class="card-body">
            <h4 class="card-title float-start">最熱門活動</h4>
            <p class="text-end"><small class="text-muted">(過去6個月)</small></p>
            <div style="max-height: 350px">
                <canvas id="heat-event" height="233" class="w-100"></canvas>
            </div>
        </div>
    </div>
</div>

最近三日評論
</div>
<script>
require.config({
    paths: {
        'chartjs': ["https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.2.1/chart.umd.min"],
        /*'chartjs-adapter-moment': ["https://cdn.jsdelivr.net/npm/chartjs-adapter-moment@1.0.1/dist/chartjs-adapter-moment.min"],
        '@amcharts/amcharts5' : ["https://cdn.amcharts.com/lib/5/index"],
        '@amcharts/amcharts5/xy' : ["https://cdn.amcharts.com/lib/5/xy"],
        '@amcharts/amcharts5/themes/Animated' : ["https://cdn.amcharts.com/lib/5/themes/Animated"],
        '@amcharts/amcharts5/locales/zh_Hant': ["https://cdn.amcharts.com/lib/5/locales/zh_Hant"]*/
    }
});
loadModules(['chartjs', 'myself/page/home']);
</script>
body;
        }

    }

    /* POST請求 */
    function post(array $data): array {
        global $auth;

        /* 數據統計 */
        if ($_GET['type'] === "count") {
            /* 年度 */
            $stmt = $this->sqlcon->prepare("SELECT m.text, IFNULL(SUM(b.pay_price), 0) AS `total`, COUNT(b.ID) AS `count`
                                                FROM months_calendar m LEFT JOIN Book_event b ON (
                                                    m.months = MONTH(b.order_datetime) AND YEAR(b.order_datetime) = YEAR(CURRENT_DATE) AND b.event_ID IN (SELECT ID FROM Event WHERE UUID = ?))
                                                GROUP BY m.months ORDER BY m.months");
            $stmt->bind_param("s", $auth->userdata['UUID']);
            if (!$stmt->execute()) {
                return array(
                    'code' => 500,
                    'Title' => 'Database Error!',
                    'Message' => $stmt->error,
                );
            }
            $output['year'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

            /* 月度 */
            $stmt->prepare("SELECT order_datetime AS `text`, IFNULL(SUM(pay_price), 0) AS `total`, COUNT(ID) AS `count` FROM Book_event 
                                WHERE YEAR(order_datetime) = YEAR(CURRENT_DATE) AND MONTH(order_datetime) = MONTH(CURRENT_DATE) AND event_ID IN (SELECT ID FROM Event WHERE UUID = ?) 
                                GROUP BY order_datetime ORDER BY text");
            $stmt->bind_param("s", $auth->userdata['UUID']);
            if (!$stmt->execute()) {
                return array(
                    'code' => 500,
                    'Title' => 'Database Error!',
                    'Message' => $stmt->error,
                );
            }

            try {
                /* 列出本月日曆 */
                $now = new Moment();
                $endDay = $now->cloning()->endOf('month');
                for ($i = 1; $i <= intval($endDay->getDay()); $i++) {
                    $now->setDay($i);
                    $output['month'][] = array(
                        'text' => $now->format('Y-m-d'),
                        'total' => 0,
                        'count' => 0,
                    );
                }

                /* 取出結果 */
                $result = $stmt->get_result();
                while ($row = $result->fetch_assoc()) {
                    $now = new Moment($row['text']);
                    $output['month'][intval($now->getDay()) - 1] = $row;
                }
            } catch (MomentException $e) {
                return array(
                    'code' => 500,
                    'Title' => showText('Error'),
                );
            }

            /* group to week */
            $temp = array();
            $i = 0;
            $week = 0;
            for ($x = 0; $x < count($output['month']); $x++) {
                $value = $output['month'][$x];
                $i++;
                if ($i === 1) {
                    $temp[$week]['text'] = $value['text'] . ' ~ ';
                    $temp[$week]['count'] = 0;
                    $temp[$week]['total'] = 0;
                }
                if ($i <= 7) {
                    $temp[$week]['total'] += $value['total'];
                    $temp[$week]['count'] += $value['count'];
                }
                if ($i === 7 || $x === count($output['month']) - 1) {
                    $temp[$week]['text'] .= $value['text'];
                    $week++;
                    $i = 0;
                }
            }
            $output['month'] = $temp;

            return array(
                'code' => 200,
                'data' => $output,
            );
        }

        /* 今日預約 */
        if($_GET['type'] === "today"){
            $stmt = $this->sqlcon->prepare("SELECT b.ID, u.Name, d.last_name, d.first_name, b.event_ID FROM Book_event b, User u, User_detail d, Event e 
                                               WHERE b.book_date = CURRENT_DATE AND b.User = u.UUID AND b.User = d.UUID AND b.event_ID = e.ID AND e.UUID = ?");
            $stmt->bind_param("s", $auth->userdata['UUID']);
            if (!$stmt->execute()) {
                return array(
                    'code' => 500,
                    'Title' => 'Database Error!',
                    'Message' => $stmt->error,
                );
            }

            $output = array();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {

                # 展示預約計劃
                $stmt->prepare("SELECT p.plan_name, b.plan_people, e.start_time, e.end_time FROM Book_event_plan b, Event_schedule e, Event_plan p 
                                    WHERE b.event_schedule = e.Schedule_ID AND e.plan = p.plan_ID AND b.Book_ID = ? AND e.Event_ID = ? AND p.Event_ID = ?");
                $stmt->bind_param('sss', $row['ID'], $row['event_ID'], $row['event_ID']);
                if (!$stmt->execute()) {
                    return array(
                        'code' => 500,
                        'Title' => 'Database Error!',
                        'Message' => $stmt->error,
                    );
                }

                //合併
                $output[] = array_merge($row, array('plan' => $stmt->get_result()->fetch_all(MYSQLI_ASSOC)));
            }

            return array('code' => 200, 'data' => $output); //output
        }

        /* 顧客國家/地區 */
        if($_GET['type'] === "country"){
            $stmt = $this->sqlcon->prepare("SELECT d.country , COUNT(b.ID) AS `count` FROM Book_event b, User_detail d 
                                                WHERE b.User = d.UUID AND b.order_datetime >= SUBDATE(CURRENT_DATE, INTERVAL 6 MONTH ) 
                                                  AND b.event_ID IN(SELECT ID FROM Event WHERE UUID = ?) GROUP BY d.country");
            $stmt->bind_param("s", $auth->userdata['UUID']);
            if (!$stmt->execute()) {
                return array(
                    'code' => 500,
                    'Title' => 'Database Error!',
                    'Message' => $stmt->error,
                );
            }

            return array('code' => 200, 'data' => $stmt->get_result()->fetch_all(MYSQLI_ASSOC)); //output
        }
        media::Generate_img_html('sds');
        /* 最熱門活動 */
        if($_GET['type'] === "top"){
            $stmt = $this->sqlcon->prepare("SELECT e.ID, e.Name, COUNT(b.ID) AS `count` FROM Book_event b, Event e 
                                                WHERE b.event_ID = e.ID AND b.order_datetime >= SUBDATE(CURRENT_DATE, INTERVAL 6 MONTH ) 
                                                  AND e.UUID = ? GROUP BY e.ID ORDER BY `count` DESC LIMIT 5");
            $stmt->bind_param("s", $auth->userdata['UUID']);
            if (!$stmt->execute()) {
                return array(
                    'code' => 500,
                    'Title' => 'Database Error!',
                    'Message' => $stmt->error,
                );
            }

            return array('code' => 200, 'data' => $stmt->get_result()->fetch_all(MYSQLI_ASSOC)); //output
        }

        return array('code' => 404, 'Message' => 'Required data request type');
    }

    /* path輸出 */
    function path(): string {
        return "<li><span>" . showText("index.home") . "</span></li>";
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