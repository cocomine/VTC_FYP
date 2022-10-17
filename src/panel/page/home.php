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

    /**
     * home constructor.
     * sql連接
     * @param $sqlcon
     */
    function __construct($sqlcon) {
        $this->sqlcon = $sqlcon;
    }

    /* 是否有權進入 */
    function access(bool $isAuth, int $role): int {
        return 200;
    }

    /* 輸出頁面 */
    function showPage(): string {
        return <<<body
        <link rel="stylesheet" href="/panel/assets/css/myself/datetimepicker.css">
        <div class='col-12 mt-4' style="height: 100vh">
            <div class="card h-100" style="background-image: url('/panel/assets/images/bg/bg/6.webp'); background-size: cover; background-position: center">
                <div class='card-body'>
                    <div class="row align-content-center h-100 text-light g-2">
                        <div class="col-12">
                            <h1>search flight</h1>
                        </div>
                        <div class="col-12 col-lg-10 col-xxl-8">
                            <div class="row align-items-center g-2">
                                <div class="form-floating col-12 col-lg ps-1">
                                    <input type="text" class="form-control form-rounded" id="Departure" placeholder="Departure">
                                    <label for="Departure">Departure</label>
                                </div>
                                <div class="col-auto">
                                    <button class="btn btn-light btn-rounded" id="reverse"><i class="fa-solid fa-arrow-right-arrow-left"></i></button>
                                </div>
                                <div class="form-floating col-12 col-lg ps-1">
                                    <input type="text" class="form-control form-rounded" id="Destination" placeholder="Destination">
                                    <label for="Destination">Destination</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-lg-10 col-xxl-8">
                            <div class="row align-items-center g-2">
                                <div class="form-floating col-12 col-lg ps-1 date-picker">
                                    <input type="date" id="Date" class="form-control form-rounded date-picker-toggle" data-bs-toggle="dropdown" placeholder="Departure" required max="31-12-2026T00:00">
                                    <label for="Date">Departure date</label>
                                </div>
                                <div class="form-floating col-12 col-lg ps-1">
                                    <select class="form-select form-rounded" aria-label="Default select example" id="Cabin">
                                        <option value="0">All class</option>
                                        <option value="1">Economy class</option>
                                        <option value="2">Business class</option>
                                    </select>
                                    <label for="Cabin">Cabin</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script>
        loadModules(['moment.min', 'myself/datatimepicker', 'myself/page/home'])
        </script>
        body;
    }

    /* POST請求 */
    function post(array $data): array {
        return array();
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