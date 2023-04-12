<?php
/*
 * Copyright (c) 2023.
 * Create by cocomine
 */

namespace page;

use cocomine\IPage;
use mysqli;

class xmap implements IPage {

    private mysqli $sqlcon;

    function __construct(mysqli $sqlcon) {
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
        return <<<HTML
<link href='https://api.mapbox.com/mapbox-gl-js/v2.12.0/mapbox-gl.css' rel='stylesheet' />
<link rel="stylesheet" href="https://api.mapbox.com/mapbox-gl-js/plugins/mapbox-gl-geocoder/v5.0.0/mapbox-gl-geocoder.css">
<div class="container mt-4">
    <div class="row gy-4">
        <div class="col-12">
            <h3>查看一下你附近都有什麼活動可以參與! (*≧︶≦))(￣▽￣* )ゞ</h3>
        </div>
        <div class="col-12">
            <div class="card overflow-hidden position-relative">
                <div id="map" style="height: 90vh"></div>
                <div class="position-absolute start-0 end-0" style="top: 5rem;">
                    <div class="row justify-content-center">
                        <button class="btn btn-rounded btn-primary col-auto shadow" id="search-map">搜尋這個區域</button>
                    </div>
                </div>
                <div class="position-absolute start-0 end-0" style="bottom: 2.5rem">
                    <div class="w-100" id="carousel-list">
                        <div class="owl-carousel owl-theme"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    require.config({
        paths:{
            '@mapbox/mapbox-gl-geocoder': ['https://api.mapbox.com/mapbox-gl-js/plugins/mapbox-gl-geocoder/v5.0.0/mapbox-gl-geocoder.min'],
            'mapbox-gl': ['https://api.mapbox.com/mapbox-gl-js/v2.12.1/mapbox-gl'],
        },
    });
    loadModules(['myself/page/xmap', 'mapbox-gl', '@mapbox/mapbox-gl-geocoder', 'owl.carousel']);
</script>
HTML;
    }

    /**
     * @inheritDoc
     */
    public function post(array $data): array {
        $stmt = $this->sqlcon->prepare("SELECT ID, name, summary, thumbnail, longitude, latitude FROM Event WHERE longitude BETWEEN ? AND ? AND latitude BETWEEN ? AND ? AND state = 1 ORDER BY create_time DESC");
        $stmt->bind_param("dddd", $data['NorthWest']['lng'], $data['SouthEast']['lng'], $data['SouthEast']['lat'], $data['NorthWest']['lat']);
        if (!$stmt->execute()) {
            return ['status' => 500, 'Message' => $stmt->error, 'Title' => showText('Error_Page.something_happened')];
        }

        $result = $stmt->get_result();
        return ['code' => 200, 'data' => $result->fetch_all(MYSQLI_ASSOC)];
    }

    /**
     * @inheritDoc
     */
    public function path(): string {
        return '<li class="breadcrumb-item"><a href="/">' . showText("index.home") . '</a></li>'
            . '<li class="breadcrumb-item active">X-Map</li>';
    }

    /**
     * @inheritDoc
     */
    public function get_Title(): string {
        return 'X-Map | X-Sport';
    }

    /**
     * @inheritDoc
     */
    public function get_Head(): string {
        return 'X-Map';
    }
}