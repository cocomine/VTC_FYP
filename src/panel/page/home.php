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
    function access(bool $isAuth, int $role, bool $isPost): int {
        return 200;
    }

    /* 輸出頁面 */
    function showPage(): string {

        $Text = showText('index.Content');

        return <<<body
        <link rel="stylesheet" href="/panel/assets/css/myself/datetimepicker.css">
        <div class='col-12 mt-4' style="height: 100vh">
            <div class="card h-100" style="background-image: url('/panel/assets/images/bg/bg/6.webp'); background-size: cover; background-position: center">
                <div class='card-body'>
                <form class="needs-validation h-100" novalidate>
                    <div class="row align-content-center h-100 text-light g-2">
                        <div class="col-12">
                            <h1>{$Text['Title']}</h1>
                        </div>
                        <div class="col-12 col-lg-10 col-xxl-8">
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
                        <div class="col-12 col-lg-10 col-xxl-8">
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
                                    <span class="input-group-text form-rounded"><i class="fa-solid fa-briefcase"></i></span>
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
                        <div class="col-12 col-lg-10 col-xxl-8">
                            <div class="row justify-content-end g-2">
                                <div class="col col-lg-6">
                                    <button class="btn btn-primary btn-rounded w-100 border border-2 border-light form-submit" type="submit"><i class="fa-solid fa-magnifying-glass me-2"></i>{$Text['Search']}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                </form>
            </div>
        </div>
        <script>
        require.config({
            paths:{
                forge: ['https://cdn.jsdelivr.net/npm/node-forge/dist/forge.min'],
            },
        });
        loadModules(['moment.min', 'myself/datatimepicker', 'myself/page/home', 'forge'])
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