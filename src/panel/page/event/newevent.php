<?php
/*
 * Copyright (c) 2023.
 * Create by cocomine
 */

namespace panel\page\event;

use cocomine\IPage;
use DateInterval;
use DateTime;
use DateTimeZone;
use panel\apis\media;

class newevent implements IPage {

    public function access(bool $isAuth, int $role, bool $isPost): int {
        if (!$isAuth) return 401;
        if ($role < 2) return 403;
        return 200;
    }

    public function showPage(): string {
        $time_zone = new DateTimeZone("Asia/Hong_Kong");
        $today = new DateTime('now', $time_zone);

        $Text = showText('Media.Content');
        $Text2 = showText('Media-upload.Content');

        $LangJson = json_encode(array(
            'No_media' => $Text['No_media'],
            'Media' => $Text['Media'] . ' %s',
            'Unknown_Error' => showText('Error'),
            'title' => $Text['Media_Select']['title'],
            'Select' => $Text['Media_Select']['Select'],
            'upload' => array(
                'Timeout' => $Text2['respond']['Timeout'],
                'File_name_over' => $Text2['respond']['File_name_over'],
                'Over_size' => $Text2['respond']['Over_size'],
                'File_type_not_mach' => $Text2['respond']['File_type_not_mach'],
                'Waiting' => $Text2['respond']['Waiting'],
                'limit_type' => $Text2['limit_type'],
                'drag' => $Text2['drag'],
                'upload' => $Text2['upload'],
                'or' => $Text2['or'],
                'limit' => $Text2['limit']
            )
        ));
        return <<<body
<link rel="stylesheet" href="https://unpkg.com/easymde/dist/easymde.min.css">
<link rel="stylesheet" href="/panel/assets/css/myself/media-select.css">
<link href='https://api.mapbox.com/mapbox-gl-js/v2.12.0/mapbox-gl.css' rel='stylesheet' />
<link rel="stylesheet" href="https://api.mapbox.com/mapbox-gl-js/plugins/mapbox-gl-geocoder/v5.0.0/mapbox-gl-geocoder.css">
<link rel="stylesheet" href="/panel/assets/css/myself/datetimepicker.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/timepicker@1.14.0/jquery.timepicker.min.css"/>
<link rel="stylesheet" href="/panel/assets/css/myself/page/event.css"/>
<pre id="media-select-LangJson" class="d-none">$LangJson</pre>
body. <<<body
<div class="alert alert-info font-14" role="alert" id="found-draft" style="display: none">
  我們在您的瀏覽器中發現了上次儲存的草稿! 要加載入來嗎?ヾ(•ω•`)o <a href="#" class="ms-2" id="load-draft">載入!</a>
</div>
<div class="col-12 col-lg-9">
    <div class="row gy-4">
        <!--活動標題-->
        <div class="col-12">
            <form class="needs-validation" novalidate id="event-form-title">
                <div class="form-floating">
                    <input type="text" class="form-control form-control-lg form-rounded" id="event-title" name="event-title" maxlength="20" required style="font-size: 1.4em; font-weight: bold" placeholder="活動標題" autofocus/>
                    <label for="event-title">活動標題</label>
                    <div class="invalid-feedback">這裏不能留空哦~~</div>
                </div>
            </form>
        </div>
        <!--活動資料-->
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">活動資料</h4>
                    <div class="card-text">
                        <form class="needs-validation" novalidate id="event-form-data">
                            <div class="col-12 mb-4">
                                <label for="event-summary" class="form-label">活動摘要</label>
                                <textarea class="form-control" name="event-summary" id="event-summary" rows="2" maxlength="50" required></textarea>
                                <span class="float-end text-secondary" id="event-summary-count" style="margin-top: -20px; margin-right: 10px">0/50</span>
                                <div class="invalid-feedback">這裏不能留空哦~~</div>
                            </div>
                            <div class="col-12 mb-2">
                                <label for="event-summary" class="form-label">活動注意事項</label>
                                <textarea class="form-control" name="event-precautions" id="event-precautions" rows="4" maxlength="200"></textarea>
                            </div>
                            <div class="col-12">
                                <label for="event-description" class="form-label">活動描述</label>
                                <textarea class="form-control" name="event-description" id="event-description" rows="5" maxlength="1000" required></textarea>
                                <div class="invalid-feedback">活動描述不能留空哦~~</div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- 活動計劃 -->
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">活動計劃</h4>
                    <div class="card-text">
                        <form class="needs-validation" novalidate id="event-form-plan"></form>
                        <button type="button" class="btn btn-rounded btn-primary" id="event-plan-add"><i class="fa-solid fa-plus me-2"></i>增加計劃</button>
                        <div class="invalid-feedback" id="event-plan-feedback">這裏至少需要一個計劃哦~~</div>
                    </div>
                </div>
            </div>
        </div>
body . <<<body
        <!-- 活動時段 -->
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">活動時段</h4>
                    <div class="card-text">
                        <form class="needs-validation" novalidate id="event-form-schedule"></form>
                        <button type="button" class="btn btn-rounded btn-primary" id="event-schedule-add"><i class="fa-solid fa-calendar-plus me-2"></i>增加時段</button>
                        <div class="invalid-feedback" id="event-schedule-feedback">這裏至少需要一個時段哦~~</div>
                    </div>
                </div>
            </div>
        </div>
        <!-- 活動圖片 -->
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">活動圖片</h4>
                    <div class="card-text">
                        <form class="needs-validation" novalidate id="event-form-image">
                            <p class="d-none d-lg-block">你可以拖拉改變次序</p>
                            <div class="media-list row mb-2 scrollbar-dynamic" id="event-image-list"></div>
                            <button type="button" class="btn btn-rounded btn-primary" id="event-image-select"><i class="fa-regular fa-object-ungroup me-2"></i>選擇圖片</button>
                            <small>你最多可以選擇五張圖片</small><br>
                            <div class="col-12">
                                <input type="text" class="d-none" id="event-image" name="event-image" required>
                                <div class="invalid-feedback">這裏至少需要選擇一張圖片哦~~</div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- 活動位置 -->
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">活動位置</h4>
                    <div class="card-text">
                        <form class="needs-validation" novalidate id="event-form-location">
                            <div class="row">
                                <div class="col-12 mb-1">
                                    <label for="event-location" class="form-label">活動詳細地址</label>
                                    <textarea class="form-control" id="event-location" name="event-location" maxlength="50" rows="2" style="resize: none;" required></textarea>
                                    <span class="float-end text-secondary" id="event-location-count" style="margin-top: -20px; margin-right: 10px">0/50</span>
                                    <div class="invalid-feedback">這裏不能留空哦~~</div>
                                </div>
                                <div class="col-6 col-sm-4 mb-4">
                                    <label for="event-location" class="form-label">國家/地區</label>
                                    <select class="form-select form-rounded crs-country" name="event-country" id="event-country" data-region-id="event-region" data-value="shortcode" data-default-option="請選擇" required>
                                    </select>
                                    <div class="invalid-feedback">這裏必須選擇哦~~</div>
                                </div>
                                <div class="col-6 col-sm-4 mb-4">
                                    <label for="event-location" class="form-label">省/州</label>
                                    <select class="form-select form-rounded" name="event-region" id="event-region" data-default-option="請選擇" required>
                                    </select>
                                    <div class="invalid-feedback">這裏必須選擇哦~~</div>
                                </div>
                                <div class="col-12 mb-3 position-relative">
                                    <label class="form-label">地圖位置</label>
                                    <div class="col-12">
                                        <input type="number" class="d-none form-control" name="event-longitude" id="event-longitude" step="0.0001" required>
                                        <input type="number" class="d-none form-control" name="event-latitude" id="event-latitude" step="0.0001" required>
                                        <div class="invalid-feedback">這裏未選擇位置哦~~</div>
                                    </div>
                                    <div class="w-100 rounded" style="min-height: 30rem" id="map"></div>
                                    <span style="bottom: 5rem;" class="position-absolute start-50">
                                        <span class="position-relative text-white bg-black bg-opacity-50 p-2 rounded" style="left: -50%;">移動標記選擇位置</span>
                                        <span id="invalid-feedback" class="position-relative text-white bg-danger bg-opacity-75 p-2 rounded" style="left: -50%; display: none">國家/地區 尚未支持</span>
                                    </span>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
body . <<<body
<div class="col-12 col-lg-3">
    <div class="row gy-4">
        <!-- 活動狀態 -->
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">活動狀態</h4>
                    <div class="card-text">
                        <form class="needs-validation" novalidate id="event-form-status">
                            <div class="col-12 mb-3">
                                <label for="event-status" class="form-label"><i class="fa-solid fa-eye me-1"></i>狀態</label>
                                <select class="form-select form-rounded form-control-sm" id="event-status" name="event-status" required>
                                    <option value="0">不公開</option>
                                    <option value="1">開放報名</option>
                                    <option value="2">暫停報名</option>
                                </select>
                            </div>
                            <div class="col-12 row g-0">
                                <label for="event-post-date" class="form-label"><i class="fa-regular fa-calendar-days me-1"></i>發佈日期</label>
                                <div class="date-picker col-7">
                                    <input type="date" class="form-control form-rounded date-picker-toggle" name="event-post-date" id="event-post-date" required>
                                    <div class="invalid-feedback">這裏不能留空哦~~</div>
                                </div>
                                <div class="col-5">
                                    <input type="text" class="form-control form-rounded" name="event-post-time" id="event-post-time" required>
                                    <div class="invalid-feedback">這裏不能留空哦~~</div>
                                </div>
                            </div>
                        </form>
                        <div class="text-end mt-3">
                            <button type="button" class="btn btn-rounded btn-secondary btn-sm" id="event-daft">儲存草稿</button>
                            <button type="button" class="btn btn-rounded btn-primary btn-sm" id="event-post">發佈</button>
                            <div class="float-start float-lg-end d-none">
                                <a class="text-danger text-decoration-underline" href="#" id="event-recycle">移到回收桶</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- 活動屬性 -->
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">活動屬性</h4>
                    <div class="card-text">
                        <form class="needs-validation" novalidate id="event-form-attribute">
                            <div class="col-12 mb-3">
                                <label for="event-type" class="form-label"><i class="fa-solid fa-bars-staggered me-1"></i>活動種類</label>
                                <select class="form-select form-rounded form-control-sm" id="event-type" name="event-type" required>
                                    <option selected value>選擇種類</option>
                                    <option value="0">水上活動</option>
                                    <option value="1">陸上活動</option>
                                    <option value="2">空中活動</option>
                                </select>
                                <div class="invalid-feedback">這裏必須選擇哦~~</div>
                            </div>
                            <label for="event-type" class="form-label"><i class="fa-solid fa-tags me-1"></i>標籤</label>
                            <div class="col-12 border border-1 rounded">
                                <div class="row m-0" id="event-tag-list">
                                    <input type="text" id="event-add-tag" class="col">
                                </div>
                                <input type="text" id="event-tag" name="event-tag" class="d-none">
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- 活動封面 -->
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">活動封面</h4>
                    <div class="card-text">
                        <form class="needs-validation" novalidate id="event-form-thumbnail">
                            <div class="col-12 text-center">
                                <img src="" alt="" class="border border-1" id="event-thumbnail-img" draggable="false" style="max-height: 10rem">
                            </div>
                            <input type="text" class="form-control d-none" name="event-thumbnail" id="event-thumbnail" required>
                            <div class="invalid-feedback">這裏需要選擇圖片哦~~</div>
                        </form>
                        <a class="text-primary text-decoration-underline" href="#" id="event-thumbnail-change">設定/更改 封面圖片</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
body . <<<body
<script>
    require.config({
        paths:{
            'mapbox-gl': ['https://api.mapbox.com/mapbox-gl-js/v2.12.0/mapbox-gl'],
            '@mapbox/mapbox-gl-geocoder': ['https://api.mapbox.com/mapbox-gl-js/plugins/mapbox-gl-geocoder/v5.0.0/mapbox-gl-geocoder.min'],
            '@mapbox/mapbox-sdk':['https://unpkg.com/@mapbox/mapbox-sdk/umd/mapbox-sdk.min'],
            easymde: ['https://unpkg.com/easymde/dist/easymde.min'],
            showdown: ['https://cdn.jsdelivr.net/npm/showdown@2.1.0/dist/showdown.min'],
            xss:['xss.min'],
            'media-select': ['myself/media-select'],
            'media-select.upload': ['myself/media-select.upload'],
            'timepicker': ['https://cdn.jsdelivr.net/npm/timepicker@1.14.0/jquery.timepicker.min']
        },
        shim: {
            xss: { exports: "filterXSS" },
        }
    })
    loadModules(['myself/page/event/newEvent', 'easymde', 'showdown','xss', 'media-select', 'media-select.upload', 'mapbox-gl', '@mapbox/mapbox-gl-geocoder', '@mapbox/mapbox-sdk', 'myself/datepicker', 'timepicker', 'jquery.crs.min'])
</script>
body;

    }

    public function post(array $data): array {
        return array();
    }

    public function path(): string {
        return "<li><a href='/panel'>" . showText("index.home") . "</a></li>
            <li><a href='/panel/event'>活動</a></li>
            <li><span>增加活動</span></li>";
    }

    public function get_Title(): string {
        return "增加活動 | X-Travel";
    }

    public function get_Head(): string {
        return "增加活動";
    }
}