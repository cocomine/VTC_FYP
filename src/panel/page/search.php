<?php
/*
 * Copyright (c) 2022.
 * Create by cocomine
 */

namespace panel\page;

use cocomine\IPage;
use mysqli;

class search implements IPage {

    private mysqli $sqlcon;
    private array $upPath;

    /**
     * @param mysqli $sqlcon
     * @param array $upPath
     */
    public function __construct(\mysqli $sqlcon, array $upPath) {
        $this->sqlcon = $sqlcon;
        $this->upPath = $upPath;
    }


    /**
     * @inheritDoc
     */
    public function access(bool $isAuth, int $role): int {
        return 200;
    }

    /**
     * @inheritDoc
     */
    public function showPage(): string {

        $cabin = intval($this->upPath[0]);
        $cabin = filterCabin($cabin) ? $cabin : '';

        $departure = $this->upPath[1];
        $destination = $this->upPath[2];
        $date = $this->upPath[3];

        return <<<body
        <link rel="stylesheet" href="/panel/assets/css/myself/datetimepicker.css">
        <div class='col-12'>
            <div class="card" style="background-image: url('/panel/assets/images/bg/bg/6.webp'); background-size: cover; background-position: center">
                <div class='card-body'>
                    <div class="row align-content-center h-100 text-light g-2">
                        <div class="col-12">
                            <div class="row align-items-center g-2 justify-content-center">
                                <div class="form-floating col-12 col-lg ps-1">
                                    <input type="text" class="form-control form-rounded" id="Departure" placeholder="Departure" required>
                                    <label for="Departure">Departure</label>
                                </div>
                                <div class="col-auto">
                                    <button class="btn btn-light btn-rounded" id="reverse"><i class="fa-solid fa-arrow-right-arrow-left"></i></button>
                                </div>
                                <div class="form-floating col-12 col-lg ps-1">
                                    <input type="text" class="form-control form-rounded" id="Destination" placeholder="Destination" required>
                                    <label for="Destination">Destination</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="row align-items-center g-2">
                                <div class="form-floating col-12 col-lg ps-1 date-picker">
                                    <input type="date" id="Date" class="form-control form-rounded date-picker-toggle" data-bs-toggle="dropdown" placeholder="Departure" required>
                                    <label for="Date">Departure date</label>
                                </div>
                                <div class="form-floating col-12 col-lg ps-1">
                                    <select class="form-select form-rounded" aria-label="Default select example" id="Cabin" required>
                                        <option value="0" {$cabin}>All class</option>
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
        <div class='col-12'>
            <div class="row g-4" id="result">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12 col-sm-2">
                                    <div class="row align-content-center h-100 justify-content-center">
                                        <h4 class="col-auto">TR125</h4>
                                        <div class="w-100"></div>
                                        <span class="col-auto">$15.54</span>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="row align-content-center h-100 pe-4">
                                        <div class="col-auto"><h3>XXX</h3></div>
                                        <div class="col row align-content-center"><div class="fly-arrow"><div></div></div></div>
                                        <div class="col-auto"><h3>XXX</h3></div>
                                    </div>
                                </div>` 
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

    /**
     * @inheritDoc
     */
    function post(array $data): array {
        return array();
    }

    /**
     * @inheritDoc
     */
    function path(): string {
        return "<li><span><a href='/panel'>" . showText("index.home") . "</a></span></li><li><span>Search Flight</span></li>";
    }

    /**
     * @inheritDoc
     */
    public function get_Title(): string {
        return 'Search Flight | IVE airline';
    }

    /**
     * @inheritDoc
     */
    public function get_Head(): string {
        return 'Search Flight';
    }

}