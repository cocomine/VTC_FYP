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

class search implements IPage {

    private mysqli $sqlcon;

    /**
     * @param mysqli $sqlcon
     * @param array $upPath
     */
    public function __construct(mysqli $sqlcon, array $upPath) {
        $this->sqlcon = $sqlcon;
    }


    /**
     * @inheritDoc
     */
    public function access(bool $isAuth, int $role, bool $isPost): int {
        return 200;
    }

    /**
     * @inheritDoc
     */
    public function showPage(): string {

        $Text = showText('Search.Content');
        $jsonLang = json_encode(array(
            'Cabin_type' => $Text['Cabin_type'],
            'Not_match' => $Text['Not_match']
        ));

        return <<<body
        <pre id='langJson' style='display: none'>$jsonLang</pre>
        <link rel="stylesheet" href="/panel/assets/css/myself/datetimepicker.css">
        <div class='col-12'>
            <div class="card" style="background-image: url('/panel/assets/images/bg/bg/6.webp'); background-size: cover; background-position: center">
                <div class='card-body'>
                <form class="needs-validation" novalidate>
                    <div class="row align-content-center h-100 text-light g-2">
                        <div class="col-12">
                            <div class="row align-items-center g-2 justify-content-center">
                                <div class="input-group col-12 col-md ps-1">
                                    <span class="input-group-text form-rounded"><i class="fa-solid fa-plane-departure ps-1"></i></span>
                                    <div class="form-floating">
                                        <input type="text" class="form-control form-rounded" list="departure-list" id="Departure" name="departure" placeholder="{$Text['Departure']}" required>
                                        <datalist id="departure-list">
                                            <option value="Hong Kong International Airport">
                                            <option value="Kansai Airports">
                                            <option value="Shanghai Pudong International Airport">
                                            <option value="Taoyuan International Airport">
                                        </datalist>
                                        <label for="Departure">{$Text['Departure']}</label>
                                        <div class="invalid-tooltip">{$Text['Form']['Cant_EMPTY']}</div>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <button class="btn btn-light btn-rounded" id="reverse" type="button"><i class="fa-solid fa-arrow-right-arrow-left"></i></button>
                                </div>
                                <div class="input-group col-12 col-md ps-1">
                                    <span class="input-group-text form-rounded"><i class="fa-solid fa-plane-arrival ps-1"></i></span>
                                    <div class="form-floating">
                                        <input type="text" class="form-control form-rounded" list="destination-list" id="Destination" name="destination" placeholder="{$Text['Destination']}" required>
                                        <datalist id="destination-list">
                                            <option value="Hong Kong International Airport">
                                            <option value="Kansai Airports">
                                            <option value="Shanghai Pudong International Airport">
                                            <option value="Taoyuan International Airport">
                                        </datalist>
                                        <label for="Destination">{$Text['Destination']}</label>
                                        <div class="invalid-tooltip">{$Text['Form']['Cant_EMPTY']}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="row align-items-center g-2">
                                <div class="input-group col-12 col-md ps-1">
                                    <span class="input-group-text form-rounded"><i class="fa-regular fa-calendar ps-1"></i></span>
                                    <div class="form-floating date-picker">
                                        <input type="date" id="Date" name="date" class="form-control form-rounded date-picker-toggle" data-bs-toggle="dropdown" placeholder="{$Text['Date']}" required>
                                        <label for="Date">{$Text['Date']}</label>
                                        <div class="invalid-tooltip">{$Text['Form']['min_date']}</div>
                                    </div>
                                </div>
                                <div class="input-group col-12 col-md ps-1">
                                    <span class="input-group-text form-rounded"><i class="fa-solid fa-briefcase ps-1"></i></span>
                                    <div class="form-floating">
                                        <select class="form-select form-rounded" aria-label="Default select example" id="Cabin" name="cabin" required>
                                            <option value="0">{$Text['Cabin_type'][0]}</option>
                                            <option value="1">{$Text['Cabin_type'][1]}</option>
                                            <option value="2">{$Text['Cabin_type'][2]}</option>
                                        </select>
                                        <label for="Cabin">{$Text['Cabin']}</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="row justify-content-end g-2">
                                <div class="col col-lg-6">
                                    <button class="btn btn-primary btn-rounded w-100 border border-2 border-light form-submit" type="submit"><i class="fa-solid fa-magnifying-glass me-2"></i>{$Text['Search']}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
                </div>
            </div>
        </div>
        <div class='col-12'>
            <div class="row g-4" id="result"></div>
        </div>
        <script>
        require.config({
            paths:{
                forge: ['https://cdn.jsdelivr.net/npm/node-forge/dist/forge.min'],
            },
        });
        loadModules(['moment.min', 'myself/datatimepicker', 'myself/page/search', 'forge'])
        </script>
        body;

    }

    /**
     * @inheritDoc
     */
    function post(array $data): array {
        $cabin = intval(filter_var(trim($data['cabin']), FILTER_SANITIZE_NUMBER_INT));
        $date = filter_var(trim($data['date']), FILTER_SANITIZE_STRING);
        $departure = filter_var(trim($data['departure']), FILTER_SANITIZE_STRING);
        $like_departure = "%" . $departure . "%";
        $destination = filter_var(trim($data['destination']), FILTER_SANITIZE_STRING);
        $like_destination = "%" . $destination . "%";

        /* 判斷今日之前 */
        $time_zone = new DateTimeZone("Asia/Hong_Kong");
        $now = new DateTime('now', $time_zone);
        $interval = DateTime::createFromFormat('Y-m-d', $date, $time_zone)->diff($now);
        if ($interval->invert == 0 && $interval->days > 0) {
            return array(
                'code' => 200,
                'data' => array(
                    'flights' => array()
                )
            );
        }

        /* 如果是本日增加時間過濾 */
        $time = '00:00:00';
        if($interval->invert == 0){
            $time = $now->format('H:i:s');
        }

        $flights = array();
        /* 取得資料 Economy */
        if($cabin == 0 || $cabin == 1) {
            $stmt = $this->sqlcon->prepare(
                "SELECT Flight.ID AS ID, Flight.Flight AS Flight, Flight.DateTime AS DateTime, Flight.`From` AS `From`, Flight.`To` AS `To`, Price.Economy AS Price
            FROM Flight, Price, Aircaft
            WHERE Flight.ID = Price.ID AND Flight.Aircaft = Aircaft.ID AND 
            Flight.ID IN(
                SELECT ID FROM Flight f WHERE f.`From`
                    IN(SELECT Code FROM Location WHERE Code LIKE ? OR Name LIKE ?)
                AND f.`To`
                    IN(SELECT Code FROM Location WHERE Code LIKE ? OR Name LIKE ?)
            ) AND DATE(DateTime) = ? AND TIME(DateTime) >= ? AND Aircaft.Economy > 0 ORDER BY DateTime DESC");
            $stmt->bind_param("ssssss", $departure, $like_departure, $destination, $like_destination, $date, $time);
            if (!$stmt->execute()) return array('code' => 500, 'Message' => showText('Error'));

            /* 處理資料 Economy */
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $flights['Economy'][] = $row;
            }
        }

        /* 取得資料 Business */
        if($cabin == 0 || $cabin == 2) {
            $stmt = $this->sqlcon->prepare(
                "SELECT Flight.ID AS ID, Flight.Flight AS Flight ,Flight.DateTime AS DateTime, Flight.`From` AS `From`, Flight.`To` AS `To`, Price.Business AS Price
            FROM Flight, Price, Aircaft
            WHERE Flight.ID = Price.ID AND Flight.Aircaft = Aircaft.ID AND 
            Flight.ID IN(
                SELECT ID FROM Flight f WHERE f.`From`
                    IN(SELECT Code FROM Location WHERE Code LIKE ? OR Name LIKE ?)
                AND f.`To`
                    IN(SELECT Code FROM Location WHERE Code LIKE ? OR Name LIKE ?)
            ) AND DATE(DateTime) = ? AND TIME(DateTime) >= ? AND Aircaft.Business > 0 ORDER BY DateTime DESC ");
            $stmt->bind_param("ssssss", $departure, $like_departure, $destination, $like_destination, $date, $time);
            if (!$stmt->execute()) return array('code' => 500, 'Message' => showText('Error'));

            /* 處理資料 Business */
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $flights['Business'][] = $row;
            }
        }

        /* output */
        return array(
            'code' => 200,
            'data' => array(
                'flights' => $flights
            )
        );
    }

    /**
     * @inheritDoc
     */
    function path(): string {
        return "<li><span><a href='/panel/'>" . showText("index.home") . "</a></span></li><li><span>" . showText('Search.Head') . "</span></li>";
    }

    /**
     * @inheritDoc
     */
    public function get_Title(): string {
        return showText('Search.Title');
    }

    /**
     * @inheritDoc
     */
    public function get_Head(): string {
        return showText('Search.Head');
    }

}