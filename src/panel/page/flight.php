<?php
/*
 * Copyright (c) 2022.
 * Create by cocomine
 */

namespace panel\page;

use cocomine\IPage;
use DateTime;
use DateTimeZone;
use mysqli;
use NumberFormatter;

class flight implements IPage {

    private mysqli $sqlcon;
    private array $upPath;
    private string $flight;
    private bool $isAuth;

    /**
     * @param mysqli $sqlcon
     * @param array $upPath
     */
    public function __construct(mysqli $sqlcon, array $upPath) {
        $this->sqlcon = $sqlcon;
        $this->upPath = $upPath;
    }

    public function access(bool $isAuth, int $role, bool $isPost): int {
        if ($isPost && !$isAuth) return 401;
        if ($isPost && $role < 1) return 403;

        if (sizeof($this->upPath) != 1) return 404;

        /* 是否在本日之後 */
        $stmt = $this->sqlcon->prepare("SELECT Flight FROM Flight WHERE ID = ? AND DateTime >= CURRENT_DATE");
        $stmt->bind_param('s', $this->upPath[0]);
        if (!$stmt->execute()) return 500;

        $result = $stmt->get_result();
        if (!$result->num_rows > 0) return 404; //不是本日期之後
        $this->flight = $result->fetch_assoc()['Flight'];
        $this->isAuth = $isAuth;
        return 200;
    }

    public function showPage(): string {
        global $auth;
        $fmt = numfmt_create('zh', NumberFormatter::DECIMAL);
        $time_zone = new DateTimeZone("Asia/Hong_Kong");

        /* 取得資料 */
        $stmt = $this->sqlcon->prepare(
            "SELECT Flight.Flight, Flight.DateTime, Flight.`From`, Flight.`To`, Price.Economy AS PriceEconomy, Price.Business AS PriceBusiness, 
            (Aircaft.Economy - (SELECT IFNULL(SUM(Economy), 0) FROM Reserve WHERE ID = Flight.ID)) AS Economy,
            (Aircaft.Business - (SELECT IFNULL(SUM(Business), 0) FROM Reserve WHERE ID = Flight.ID)) AS Business,
            (SELECT Name FROM Location WHERE Code = Flight.`From`) AS FromStr,
            (SELECT Name FROM Location WHERE Code = Flight.`To`) AS ToStr
        FROM Flight, Price, Aircaft WHERE Flight.ID = ? AND Price.ID = Flight.ID AND Flight.Aircaft = Aircaft.ID");
        $stmt->bind_param('s', $this->upPath[0]);
        if (!$stmt->execute()) return "";

        /* 處理資料 */
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $row['Business'] = max($row['Business'], 0);
        $row['Economy'] = max($row['Economy'], 0);
        $dateTime = DateTime::createFromFormat('Y-m-d H:i:s', $row['DateTime'], $time_zone);
        $row['DateTime'] = $dateTime->format('j M Y - g:i A');

        /* js資料 */
        $dataJson = json_encode(array(
            'FromStr' => $row['FromStr'],
            'ToStr' => $row['ToStr'],
            'PriceBusiness' => $row['PriceBusiness'],
            'PriceEconomy' => $row['PriceEconomy'],
            'Economy' => $row['Economy'],
            'Business' => $row['Business'],
        ));

        /* 檢查是否已預約 */
        $isReserve = array(
            'css' => '',
            'alert' => 'd-none',
            'Business' => 0,
            'Economy' => 0,
            'Meal' => '',
            'button' => '',
            'total' => '0'
        );
        if ($this->isAuth) {
            $stmt = $this->sqlcon->prepare("SELECT * FROM Reserve WHERE UUID = ? AND ID = ?");
            $stmt->bind_param("si", $auth->userdata['UUID'], $this->upPath[0]);
            if (!$stmt->execute()) return "";

            $result = $stmt->get_result();
            if ($result->num_rows >= 1) {
                $tmp = $result->fetch_assoc();
                $isReserve['css'] = 'cover';
                $isReserve['alert'] = '';
                $isReserve['Business'] = $tmp['Business'];
                $isReserve['Economy'] = $tmp['Economy'];
                $isReserve['Meal'] = $tmp['Meal'] ? 'checked' : '';
                $isReserve['button'] = 'disabled';
                $isReserve['total'] = $fmt->format(($row['PriceBusiness'] * $tmp['Business'] + $row['PriceEconomy'] * $tmp['Economy']));;
            }
        }

        /* format */
        $row['PriceBusiness'] = $fmt->format($row['PriceBusiness']);
        $row['PriceEconomy'] = $fmt->format($row['PriceEconomy']);

        /* json */
        $Text = showText('Flight.Content');
        $LangJson = json_encode(array(
            'Need_reserve' => $Text['Confirm_Reserve']['Need_reserve'],
            'No_Need_reserve' => $Text['Confirm_Reserve']['No_Need_reserve'],
            'Need_login' => $Text['Need_login']
        ));

        return <<<body
<!--pre id='langJson' style='display: none'>{}</pre-->
<pre id='DataJson' style='display: none'>$dataJson</pre>
<pre id='LangJson' style='display: none'>$LangJson</pre>
<link href='https://api.mapbox.com/mapbox-gl-js/v2.11.0/mapbox-gl.css' rel='stylesheet' />
<div class='col-12 mt-4 col-md-8'>
    <div class="row gy-4 gx-0 m-0">
        <div class='col-12'>
            <div class="card">
                <div class="card-body">
                    <h4 class="header-title">{$row['Flight']}</h4>
                    <div class="row">
                        <div class="col">
                            <div class="row align-content-center h-100 pe-4">
                                <div class="col-auto"><h3>{$row['From']}</h3></div>
                                <div class="col row align-content-center"><div class="fly-arrow"><div></div></div></div>
                                <div class="col-auto"><h3>{$row['To']}</h3></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class='col-12'>
            <div class="card" id="map" style="min-height: 30rem">
            </div>
        </div>
        <div class='col-12'>
            <div class="card {$isReserve['css']}" id="Reserve">
                <div class="card-body">
                    <h5 class="header-title">{$Text['Reserve_Seat']}</h5>
                    <div class="alert alert-warning {$isReserve['alert']}" style="z-index: 10" role="alert"><i class="fa-solid fa-triangle-exclamation me-2"></i>{$Text['alert']}</div>
                    <div style="background-color: lightgray" class="rounded p-1">
                        <div class="row justify-content-sm-between align-items-center justify-content-center">
                            <h5 class="col-auto">{$Text['Cabin_type'][1]}</h5>
                            <div class="col-auto">
                                <div class="row align-items-center">
                                    <div class="col-auto"><button type="button" class="btn btn-primary btn-rounded" data-reserve="Business-add"><i class="fa-solid fa-plus"></i></button></div>
                                    <h6 class="col-auto" id="Business-count">{$isReserve['Business']}</h6>
                                    <div class="col-auto"><button type="button" class="btn btn-outline-primary btn-rounded" data-reserve="Business-sub"><i class="fa-solid fa-minus"></i></button></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div style="background-color: lightgray" class="mt-2 rounded p-1">
                        <div class="row justify-content-sm-between align-items-center justify-content-center">
                            <h5 class="col-auto">{$Text['Cabin_type'][0]}</h4>
                            <div class="col-auto">
                                <div class="row align-items-center">
                                    <div class="col-auto"><button type="button" class="btn btn-primary btn-rounded" data-reserve="Economy-add"><i class="fa-solid fa-plus"></i></button></div>
                                    <h6 class="col-auto" id="Economy-count">{$isReserve['Economy']}</h6>
                                    <div class="col-auto"><button type="button" class="btn btn-outline-primary btn-rounded" data-reserve="Economy-sub"><i class="fa-solid fa-minus"></i></button></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div style="background-color: lightgray" class="mt-2 rounded p-1 py-2">
                        <div class="row justify-content-sm-between align-items-center justify-content-center">
                            <h5 class="col-auto">{$Text['reserve_meal']}</h4>
                            <div class="col-auto form-check form-switch">
                                <input class="form-check-input mt-0" style="font-size: 1.5rem" role="switch" type="checkbox" id="Meal" {$isReserve['Meal']}>
                            </div>
                        </div>
                    </div>
                    <div class="row justify-content-between align-items-center mt-2 p-1">
                        <h4 class="col-auto" id="total">$ {$isReserve['total']}</h4>
                        <div class="col-auto">
                        <div class="form-check">
                            <button type="button" class="btn btn-primary btn-rounded" id="checkout" {$isReserve['button']}><i class="fa-solid fa-cart-shopping me-2"></i>{$Text['Reserve']}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class='col-12 mt-4 col-md-4'>
    <div class="row gy-4 gx-0 m-0 sticky-top">
        <div class='col-12 d-none d-md-block'>
            <div class="card">
                <div class='card-body'>
                    <div class="row g-3 justify-content-center">
                        <div class="col-12">
                            <span>{$Text['Cabin_type'][1]}</span>
                            <h4>$ {$row['PriceBusiness']}</h4>
                        </div>
                        <div class="col-12">
                            <span>{$Text['Cabin_type'][0]}</span>
                            <h4>$ {$row['PriceEconomy']}</h4>
                        </div>
                        <button class="btn btn-rounded btn-primary col-6 rout"><i class="fa-solid fa-arrow-right me-2"></i>{$Text['Go_Reserve']}</button>
                        <div class="col-12">
                            <h4>{$row['DateTime']}</h4>
                            <span>{$Text['Departure_time']}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class='col-12'>
            <div class="card">
                <div class='card-body'>
                    <h5 class="header-title">{$Text['Remaining_Seats']}</h5>
                    <div class="row g-3 justify-content-center">
                        <div class="col-12">
                            <h4>{$row['Business']}</h4>
                            <span>{$Text['Cabin_type'][1]}</span>
                        </div>
                        <div class="col-12">
                            <h4>{$row['Economy']}</h4>
                            <span>{$Text['Cabin_type'][0]}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="sticky-bottom bg-light p-3 d-md-none" id="fixed-price">
    <div class="row">
        <div class="col-12 col-sm-auto row justify-content-around justify-content-sm-start">
            <div class="col-auto">
                <h4>$ {$row['PriceBusiness']}</h4>
                <span>{$Text['Cabin_type'][1]}</span>
            </div>
            <div class="col-auto">
                <h4>$ {$row['PriceEconomy']}</h4>
                <span>{$Text['Cabin_type'][0]}</span>
            </div>
        </div>
        <div class="col-12 col-sm row justify-content-around justify-content-sm-end gy-1 gy-sm-0">
            <div class="col-auto text-end">
                <h4>{$row['DateTime']}</h4>
                <span>{$Text['Departure_time']}</span>
            </div>
            <button class="btn btn-rounded btn-primary col-auto rout"><i class="fa-solid fa-cart-shopping me-2"></i>{$Text['Go_Reserve']}</button>
        </div>
    </div>
</div>
<div id='Confirm-modal' class='modal fade' tabindex='-1'>
    <div class='modal-dialog modal-dialog-centered'>
        <div class='modal-content'>
            <div class='modal-header'>
                <h5 class='modal-title'><b>{$Text['Confirm_Reserve']['title']}</b></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class='modal-body'>
                <p>{$Text['Confirm_Reserve']['description']}</p>
                <div class="row gap-2 mx-2">
                    <div class="col text-center rounded p-1" style="background-color: lightgray">
                        <h4 id="Confirm-Business">5</h4>
                        <span>{$Text['Cabin_type'][1]}</span>
                    </div>
                    <div class="col text-center rounded p-1" style="background-color: lightgray">
                        <h4 id="Confirm-Economy">5</h4>
                        <span>{$Text['Cabin_type'][0]}</span>
                    </div>
                    <div class="col-12 rounded p-2" style="background-color: lightgray">
                        <h5 id="Confirm-meal"></h5>
                    </div>
                </div>
            </div>
            <div class='modal-footer'>
                <button class='btn btn-rounded btn-primary' id="confirm"><i class='fa-solid fa-plane-circle-check pe-2'></i>{$Text['Confirm_Reserve']['Confirm']}</button>
            </div>
        </div>
    </div>
</div>
<script>
    require.config({
        paths:{
            mapbox: ['https://api.mapbox.com/mapbox-gl-js/v2.11.0/mapbox-gl'],
            mapboxSdk: ['https://unpkg.com/@mapbox/mapbox-sdk/umd/mapbox-sdk.min'],
            turf: ['https://unpkg.com/@turf/turf@6/turf.min']
        },
    });
    loadModules(['mapbox', 'mapboxSdk', 'turf', 'myself/page/flight', 'myself/map-auto-fit'])
</script>
body;
    }

    function post(array $data): array {
        global $auth;

        //檢查是否符合要求
        if ($data['Economy'] <= 0 && $data['Business'] <= 0) return array(
            'code' => 400,
            'Title' => "Reserve Failed",
            'Message' => "Failed to process request, please try again later"
        );

        $stmt = $this->sqlcon->prepare("INSERT INTO Reserve (ID, UUID, Business, Economy, Meal) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param('isiii', $this->upPath[0], $auth->userdata['UUID'], $data['Business'], $data['Economy'], $data['meal']);
        if (!$stmt->execute()) return array(
            'code' => 400,
            'Title' => showText("Flight.Content.Fail.0"),
            'Message' => showText("Flight.Content.Fail.1")
        );

        //check
        if ($stmt->affected_rows >= 1) {
            return array(
                'code' => 200,
                'Title' => showText("Flight.Content.Success.0"),
                'Message' => showText("Flight.Content.Success.1")
            );
        } else {
            return array(
                'code' => 400,
                'Title' => showText("Flight.Content.Fail.0"),
                'Message' => showText("Flight.Content.Fail.1")
            );
        }
    }

    function path(): string {
        return "<li><span><a href='/panel/'>" . showText("index.home") . "</a></span></li><li><span><a href='/panel/search/'>" . showText('Search.Head') . "</a></span></li><li><span>" . strtoupper($this->flight) . "</span></li>";
    }

    public function get_Title(): string {
        return strtoupper($this->flight) . " " . showText('Flight.Title');
    }

    public function get_Head(): string {
        return showText('Flight.Head') . " " . strtoupper($this->flight);
    }


}