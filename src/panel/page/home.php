<?php
/**
 * Copyright (c) 2020.
 * Create by cocomine
 */

namespace panel\page;

use Cassandra\Date;
use cocomine\IPage;
use DateTime;
use Moment\Moment;
use Moment\MomentException;
use mysqli;

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
                <div class="seofct-icon"><i class="fa-solid fa-receipt"></i>本年預約</div>
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
                <div class="seofct-icon"><i class="fa-solid fa-receipt"></i>本月預約</div>
                <h2 id="month-order">--</h2>
            </div>
            <canvas id="month-order-chart" height="50"></canvas>
        </div>
    </div>
</div>
body . <<<body
<div class="col-xl-8">
    <div class="card">
        <div class="card-body">
            <h4 class="card-title">今天預約訂單</h4>
            <div class="table-responsive">
                <table class="table table-striped table-bordered zero-configuration">
                    <thead class="table-primary text-light" style="--bs-table-bg: var(--primary-color)">
                    <tr>
                        <th>#</th>
                        <th>用戶</th>
                        <th>預約時間</th>
                        <th>活動計劃 / 預約人數</th>
                    </tr>
                    </thead>
                    <tbody id="today-order"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
來自國家/地區統計chart

最熱門活動chart
最近三日評論
</div>
<script>
require.config({
    paths: {
        'chartjs': ["https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.2.1/chart.umd.min"],
        //'chartjs-adapter-moment': ["https://cdn.jsdelivr.net/npm/chartjs-adapter-moment@1.0.1/dist/chartjs-adapter-moment.min"],
    }
});
loadModules(['chartjs', 'myself/page/home'])
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
                                                    m.months = MONTH(b.book_date) AND YEAR(b.book_date) = YEAR(CURRENT_DATE) AND b.event_ID IN (SELECT ID FROM Event WHERE UUID = ?))
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
            $stmt->prepare("SELECT book_date AS `text`, IFNULL(SUM(pay_price), 0) AS `total`, COUNT(ID) AS `count` FROM Book_event 
                                WHERE YEAR(book_date) = YEAR(CURRENT_DATE) AND MONTH(book_date) = MONTH(CURRENT_DATE) AND event_ID IN (SELECT ID FROM Event WHERE UUID = ?) 
                                GROUP BY book_date ORDER BY text");
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

            $temp = array();
            $i = 0;
            $week = 0;

            foreach ($output['month'] as $value) {
                $i++;
                if ($i === 1) {
                    $temp[$week]['text'] = $value['text'];
                }
                if ($i <= 7) {
                    $temp[$week]['total'] = $temp[$week]['total'] + $value['total'];
                    $temp[$week]['count'] = $temp[$week]['count'] + $value['count'];
                }
                if ($i === 7) {
                    $temp[$week]['text'] = $temp[$week]['text'] . ' ~ ' . $value['text'];
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