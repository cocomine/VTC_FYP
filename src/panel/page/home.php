<?php
/**
 * Copyright (c) 2020.
 * Create by cocomine
 */

namespace panel\page;

use cocomine\IPage;
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
        if(!$isAuth) return 401;
        if($role < 1) return 403;
        return 200;
    }

    /* 輸出頁面 */
    function showPage(): string {

        $Text = showText('index.Content');

        if($this->role < 2){
            return "<script>location.replace('/')</script>";
        }else{
            return <<<body
<div class="col-md-6 col-lg-3 mt-5 mb-3">
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
<div class="col-md-6 col-lg-3 mt-md-5 mb-3">
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
<div class="col-md-6 col-lg-3 mt-md-5 mb-3">
    <div class="card">
        <div class="seo-fact sbg3">
            <div class="p-4 d-flex justify-content-between align-items-center">
                <div class="seofct-icon"><i class="fa-solid fa-receipt"></i>本月賺取</div>
                <h2>$ <span id="month-earned">--</span></h2>
            </div>
            <canvas id="month-earned-chart" height="50"></canvas>
        </div>
    </div>
</div>
<div class="col-md-6 col-lg-3 mt-md-5 mb-3">
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

今天預約訂單list
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

        if($_GET['type'] === "count"){

        }
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