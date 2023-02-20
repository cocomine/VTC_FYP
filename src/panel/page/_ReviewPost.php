<?php
/*
 * Copyright (c) 2023.
 * Create by cocomine
 */

namespace panel\page;

use cocomine\IPage;
use mysqli;

class _ReviewPost implements IPage {

    private mysqli $sqlcon;
    private array $upPath;

    public function __construct(mysqli $conn, array $upPath) {
        $this->sqlcon = $conn;
        $this->upPath = $upPath;
    }

    public function access(bool $isAuth, int $role, bool $isPost): int {
        global $auth;

        if (!$isAuth) return 401;
        if ($role < 2) return 403;

        //check the event is true owner
        if (sizeof($this->upPath) > 0 && preg_match("/[0-9]+/", $this->upPath[0])) {
            $stmt = $this->sqlcon->prepare("SELECT COUNT(ID) AS 'count' FROM Event WHERE ID = ? AND UUID = ?");
            $stmt->bind_param('ss', $this->upPath[0], $auth->userdata['UUID']);
            if (!$stmt->execute()) return 500;

            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            if ($row['count'] <= 0) return 403;
        }
        return 200;
    }

    public function showPage(): string {
        return <<<body
<link rel="stylesheet" href="/panel/assets/css/easymde.min.css">
<link href='https://api.mapbox.com/mapbox-gl-js/v2.12.0/mapbox-gl.css' rel='stylesheet' />
<link rel="stylesheet" href="https://api.mapbox.com/mapbox-gl-js/plugins/mapbox-gl-geocoder/v5.0.0/mapbox-gl-geocoder.css">
<link rel="stylesheet" href="/panel/assets/css/myself/page/event.css"/>
body. <<<body
<div class="col-12 col-lg-9">
    <div class="row gy-4">
        <!--活動標題-->
        <div class="col-12">
            <form class="needs-validation" novalidate id="event-form-title">
                <div class="form-floating">
                    <input type="text" class="form-control form-control-lg form-rounded" id="event-title" name="event-title" maxlength="50" required style="font-size: 1.4em; font-weight: bold" placeholder="活動標題" autofocus/>
                    <label for="event-title">活動標題</label>
                    <span class="float-end text-secondary" id="event-title-count" style="margin-top: -20px; margin-right: 18px">0/50</span>
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
                                <textarea class="form-control" name="event-summary" id="event-summary" rows="2" maxlength="80" required></textarea>
                                <span class="float-end text-secondary" id="event-summary-count" style="margin-top: -20px; margin-right: 10px">0/80</span>
                                <div class="invalid-feedback">這裏不能留空哦~~</div>
                            </div>
                            <div class="col-12 mb-2">
                                <label for="event-summary" class="form-label">活動注意事項</label>
                                <textarea class="form-control" name="event-precautions" id="event-precautions" rows="4" maxlength="500"></textarea>
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
                                    <textarea class="form-control" id="event-location" name="event-location" maxlength="100" rows="2" style="resize: none;" required></textarea>
                                    <span class="float-end text-secondary" id="event-location-count" style="margin-top: -20px; margin-right: 10px">0/100</span>
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
                                    <option value="0">排程</option>
                                    <option value="1">公開</option>
                                    <option value="2">不公開</option>
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
                            <div class="float-start my-1" id="event-recycle" style="display: none">
                                <button type="button" class="btn btn-link text-danger" data-bs-toggle="modal" data-bs-target="#delete_modal">刪除</button>
                            </div>
                            <button type="button" class="btn btn-rounded btn-secondary btn-sm my-1" id="event-daft">儲存草稿</button>
                            <button type="button" class="btn btn-rounded btn-primary btn-sm my-1" id="event-post">發佈</button>
                            <button type="button" class="btn btn-rounded btn-primary btn-sm my-1" id="event-update" style="display: none">更新</button>
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
                                    <input type="text" id="event-add-tag" class="col" maxlength="100">
                                </div>
                                <input type="text" id="event-tag" name="event-tag" class="d-none">
                            </div>
                            <small>請在每個標籤後輸入英文逗號</small>
                            <span class="float-end text-secondary" id="event-tag-count">0/100</span>
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
<div class="modal" tabindex="-1" id="delete_modal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">刪除?</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>確認刪除此活動? 您將無法恢復此操作</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-rounded" data-bs-dismiss="modal"><i class='fa fa-arrow-left pe-2'></i>取消</button>
        <button type="button" class="btn btn-danger btn-rounded" id="event-delete"><i class="fa-solid fa-trash-can pe-2"></i>確認</button>
      </div>
    </div>
  </div>
</div>
<script>
    require.config({
        paths:{
            'mapbox-gl': ['https://api.mapbox.com/mapbox-gl-js/v2.12.1/mapbox-gl'],
            '@mapbox/mapbox-gl-geocoder': ['https://api.mapbox.com/mapbox-gl-js/plugins/mapbox-gl-geocoder/v5.0.0/mapbox-gl-geocoder.min'],
            '@mapbox/mapbox-sdk':['https://unpkg.com/@mapbox/mapbox-sdk/umd/mapbox-sdk.min'],
            easymde: ['myself/easymde.min'],
            showdown: ['https://cdn.jsdelivr.net/npm/showdown@2.1.0/dist/showdown.min'],
            xss:['xss.min'],
        },
        shim: {
            xss: { exports: "filterXSS" },
        }
    })
    loadModules(['myself/page/review/ReviewPost', 'easymde', 'showdown','xss', 'mapbox-gl', '@mapbox/mapbox-gl-geocoder', '@mapbox/mapbox-sdk'], 
        (post) => {
            post.found_draft();
        })
</script>
body;

    }

    public function post(array $data): array {
        global $auth;

        // load event data
        $output = array('id' => $this->upPath[0]);

        /* event table */
        $stmt = $this->sqlcon->prepare("SELECT * FROM Event WHERE UUID = ? AND ID = ?");
        $stmt->bind_param('ss', $auth->userdata['UUID'], $this->upPath[0]);
        if (!$stmt->execute()) {
            return array(
                'code' => 500,
                'Title' => 'Database Error!',
                'Message' => $stmt->error,
            );
        }

        $row = $stmt->get_result()->fetch_assoc();
        $output['title']['event-title'] = $row['name'];
        $output['thumbnail']['event-thumbnail'] = $row['thumbnail'];
        $output['data'] = array(
            'event-description' => $row['description'],
            'event-precautions' => $row['precautions'],
            'event-summary' => $row['summary'],
        );
        $output['attribute'] = array(
            'event-tag' => $row['tag'],
            'event-type' => $row['type'],
        );
        $output['location'] = array(
            'event-country' => $row['country'],
            'event-latitude' => $row['latitude'],
            'event-longitude' => $row['longitude'],
            'event-location' => $row['location'],
            'event-region' => $row['region'],
        );
        $output['status'] = array(
            'event-post-date' => explode(' ', $row['post_time'])[0], //split to date
            'event-post-time' => explode(' ', $row['post_time'])[1], //split to time
            'event-status' => $row['state'],
        );

        /* event img */
        $stmt->prepare("SELECT media_ID FROM Event_img WHERE event_ID = ? ORDER BY `order`");
        $stmt->bind_param("s", $this->upPath[0]);
        if (!$stmt->execute()) {
            return array(
                'code' => 500,
                'Title' => 'Database Error!',
                'Message' => $stmt->error,
            );
        }

        $rows = $stmt->get_result()->fetch_all();
        $output['image']['event-image'] = join(',', array_map(fn($value): string => $value[0], $rows)); //join 1 line string

        /* event plan */
        $stmt->prepare("SELECT * FROM Event_plan WHERE Event_ID = ?");
        $stmt->bind_param("s", $this->upPath[0]);
        if (!$stmt->execute()) {
            return array(
                'code' => 500,
                'Title' => 'Database Error!',
                'Message' => $stmt->error,
            );
        }

        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $output['plan'] = array_map(fn($value): array => array(
            'id' => $value['plan_ID'],
            'max' => $value['max_people'],
            'max_each' => $value['max_each_user'],
            'name' => $value['plan_name'],
            'price' => $value['price'],
        ), $rows);

        /* event schedule */
        $stmt->prepare("SELECT * FROM Event_schedule WHERE Event_ID = ?");
        $stmt->bind_param("s", $this->upPath[0]);
        if (!$stmt->execute()) {
            return array(
                'code' => 500,
                'Title' => 'Database Error!',
                'Message' => $stmt->error,
            );
        }

        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $output['schedule'] = array_map(fn($value): array => array(
            'id' => $value['Schedule_ID'],
            'plan' => $value['plan'],
            'start' => $value['start_date'],
            'end' => $value['end_date'],
            'time_start' => $value['start_time'],
            'time_end' => $value['end_time'],
            'type' => $value['type'],
            'week' => json_decode($value['repeat_week']),
        ), $rows);

        //output
        return array('code' => 200, 'data' => $output);
    }

    public function path(): string {
        return "<li><a href='/panel'>" . showText("index.home") . "</a></li>
            <li><span>審核活動</span></li>";
    }

    public function get_Title(): string {
        return "審核活動 | X-Travel";
    }

    public function get_Head(): string {
        return "審核活動";
    }
}