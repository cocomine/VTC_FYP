<?php
/*
 * Copyright (c) 2023.
 * Create by cocomine
 */

namespace page;

use mysqli;

class xmap implements \cocomine\IPage {

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
            <div class="card overflow-hidden position-relative">
                <div id="map" style="height: 80vh"></div>
                <div class="position-absolute start-0 end-0" style="top: 5rem">
                    <div class="row justify-content-center">
                        <button class="btn btn-rounded btn-primary col-auto shadow">搜尋這個區域</button>
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
    loadModules(['myself/page/xmap', 'mapbox-gl', '@mapbox/mapbox-gl-geocoder']);
</script>
HTML;
    }

    /**
     * @inheritDoc
     */
    public function post(array $data): array {
        return $data;
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
        return 'X-Map | X-Travel';
    }

    /**
     * @inheritDoc
     */
    public function get_Head(): string {
        return 'X-Map';
    }
}