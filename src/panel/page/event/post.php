<?php
/*
 * Copyright (c) 2023.
 * Create by cocomine
 */

namespace panel\page\event;

use cocomine\IPage;
use cocomine\Parsedown_ext;
use HTMLPurifier;
use HTMLPurifier_Config;
use mysqli;

class post implements IPage {

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
<link rel="stylesheet" href="/panel/assets/css/easymde.min.css">
<link rel="stylesheet" href="/panel/assets/css/myself/media-select.css">
<link href='https://api.mapbox.com/mapbox-gl-js/v2.12.0/mapbox-gl.css' rel='stylesheet' />
<link rel="stylesheet" href="https://api.mapbox.com/mapbox-gl-js/plugins/mapbox-gl-geocoder/v5.0.0/mapbox-gl-geocoder.css">
<link rel="stylesheet" href="/panel/assets/css/myself/datetimepicker.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/timepicker@1.14.0/jquery.timepicker.min.css"/>
<link rel="stylesheet" href="/panel/assets/css/myself/page/event.css"/>
<pre id="media-select-LangJson" class="d-none">$LangJson</pre>
body. <<<body
<div class="alert alert-info alert-dismissible fade show" role="alert" id="found-draft" style="display: none">
  <p>我們在您的瀏覽器中發現了上次儲存的草稿! 要加載入來嗎?ヾ(•ω•`)o <a href="#" class="ms-2" id="load-draft" data-bs-dismiss="alert">載入!</a></p>
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
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
            'media-select': ['myself/media-select'],
            'media-select.upload': ['myself/media-select.upload'],
            'timepicker': ['https://cdn.jsdelivr.net/npm/timepicker@1.14.0/jquery.timepicker.min']
        },
        shim: {
            xss: { exports: "filterXSS" },
        }
    })
    loadModules(['myself/page/event/post', 'easymde', 'showdown','xss', 'media-select', 'media-select.upload', 'mapbox-gl', '@mapbox/mapbox-gl-geocoder', '@mapbox/mapbox-sdk', 'myself/datepicker', 'timepicker', 'jquery.crs.min'], 
        (post) => {
            post.found_draft();
        })
</script>
body;

    }

    public function post(array $data): array {
        global $auth;

        //new event
        if ($_GET['type'] === 'post') {
            $data = $this->serializeData($data);

            /* 這裏會進行輸入檢查,但非公開網頁跳過 */

            //儲存數據 Event
            $stmt = $this->sqlcon->prepare(
                "INSERT INTO Event (UUID, state, type, tag, name, thumbnail, summary, precautions, precautions_html, description, 
                   description_html, location, country, region, longitude, latitude, post_time) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
            $stmt->bind_param("siisssssssssssdds", $auth->userdata['UUID'], $data['status']['event-status'], $data['attribute']['event-type'], $data['attribute']['event-tag'],
                $data['title']['event-title'], $data['thumbnail']['event-thumbnail'], $data['data']['event-summary'], $data['data']['event-precautions'], $data['data']['event-precautions-html'],
                $data['data']['event-description'], $data['data']['event-description-html'], $data['location']['event-location'], $data['location']['event-country'], $data['location']['event-region'],
                $data['location']['event-longitude'], $data['location']['event-latitude'], $data['status']['event-post']);
            if (!$stmt->execute()) {
                return array(
                    'code' => 500,
                    'Title' => 'Database Error!',
                    'Message' => $stmt->error,
                );
            }

            //取得 Event ID
            $stmt->prepare("SELECT LAST_INSERT_ID() AS ID;");
            if (!$stmt->execute()) {
                return array(
                    'code' => 500,
                    'Title' => 'Database Error! 0',
                    'Message' => $stmt->error,
                );
            }
            $result = $stmt->get_result()->fetch_assoc();
            $event_id = $result['ID'];

            //儲存數據 Event_img
            $stmt->prepare("INSERT INTO Event_img (event_ID, media_ID, `order`) VALUES (?, ?, ?)");
            $tmp_img_order = 0;
            foreach ($data['image']['event-image'] as $image) {
                $stmt->bind_param("ssi", $event_id, $image, $tmp_img_order);
                if (!$stmt->execute()) {
                    return array(
                        'code' => 500,
                        'Title' => 'Database Error! 1',
                        'Message' => $stmt->error,
                    );
                }
                $tmp_img_order++;
            }

            //儲存數據 Event_plan
            $stmt->prepare("INSERT INTO Event_plan (Event_ID, plan_ID, plan_name, price, max_people, max_each_user) VALUES (?, ?, ?, ?, ?, ?)");
            foreach ($data['plan'] as $plan) {
                //轉換數據類型
                $plan['id'] = intval($plan['id']);
                $plan['max'] = intval($plan['max']);
                $plan['max_each'] = intval($plan['max_each']);
                $plan['price'] = floatval($plan['price']);

                $stmt->bind_param("iisdii", $event_id, $plan['id'], $plan['name'], $plan['price'], $plan['max'], $plan['max_each']);
                if (!$stmt->execute()) {
                    return array(
                        'code' => 500,
                        'Title' => 'Database Error! 2',
                        'Message' => $stmt->error,
                    );
                }
            }

            //儲存數據 Event_schedule
            $stmt->prepare("INSERT INTO Event_schedule (Event_ID, Schedule_ID, type, plan, start_date, end_date, start_time, end_time, repeat_week) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            foreach ($data['schedule'] as $schedule) {
                //轉換數據類型
                $schedule['id'] = intval($schedule['id']);
                $schedule['type'] = intval($schedule['type']);
                $schedule['plan'] = intval($schedule['plan']);
                $schedule['end'] = $schedule['end'] === "" ? null : $schedule['end'];
                $schedule['week'] = $schedule['week'] !== "" ? json_encode($schedule['week']) : null;

                $stmt->bind_param("iiiisssss", $event_id, $schedule['id'], $schedule['type'], $schedule['plan'], $schedule['start'], $schedule['end'], $schedule['time_start'], $schedule['time_end'], $schedule['week']);
                if (!$stmt->execute()) {
                    return array(
                        'code' => 500,
                        'Title' => 'Database Error! 3',
                        'Message' => $stmt->error,
                    );
                }
            }

            return array(
                'code' => 200,
                'Message' => "活動已成功添加!"
            );
        }

        //delete state event
        if ($_GET['type'] === 'del') {
            $stmt = $this->sqlcon->prepare("DELETE FROM Event WHERE ID = ? AND UUID = ?");
            $stmt->bind_param('ss', $data['id'], $auth->userdata['UUID']);
            if (!$stmt->execute()) {
                return array(
                    'code' => 500,
                    'Title' => 'Database Error!',
                    'Message' => $stmt->error,
                );
            }

            if ($stmt->affected_rows > 0) {
                return array(
                    'code' => 200,
                    'Title' => '刪除成功!',
                );
            } else {
                return array(
                    'code' => 500,
                    'Title' => '刪除失敗!',
                );
            }
        }

        //update event
        if ($_GET['type'] === 'update') {
            $post_id = intval($data['id']);
            $data = $this->serializeData($data['data']);

            /* 這裏會進行輸入檢查,但非公開網頁跳過 */

            //儲存數據 Event
            $stmt = $this->sqlcon->prepare(
                "UPDATE Event SET state=?, type=?, tag=?, name=?, thumbnail=?, summary=?, precautions=?, precautions_html=?, description=?, 
                   description_html=?, location=?, country=?, region=?, longitude=?, latitude=?, post_time=? WHERE UUID = ? AND ID = ?");
            $stmt->bind_param("iisssssssssssddssi", $data['status']['event-status'], $data['attribute']['event-type'], $data['attribute']['event-tag'],
                $data['title']['event-title'], $data['thumbnail']['event-thumbnail'], $data['data']['event-summary'], $data['data']['event-precautions'], $data['data']['event-precautions-html'],
                $data['data']['event-description'], $data['data']['event-description-html'], $data['location']['event-location'], $data['location']['event-country'], $data['location']['event-region'],
                $data['location']['event-longitude'], $data['location']['event-latitude'], $data['status']['event-post'], $auth->userdata['UUID'], $post_id);
            if (!$stmt->execute()) {
                return array(
                    'code' => 500,
                    'Title' => 'Database Error!',
                    'Message' => $stmt->error,
                );
            }

            //刪除舊數據
            $stmt->prepare("DELETE Event_img, Event_plan, Event_schedule FROM Event_img, Event_plan, Event_schedule WHERE Event_img.event_ID = ? OR Event_plan.Event_ID = ? OR Event_schedule.Event_ID = ?");
            $stmt->bind_param('iii', $post_id, $post_id, $post_id);
            if (!$stmt->execute()) {
                return array(
                    'code' => 500,
                    'Title' => 'Database Error!',
                    'Message' => $stmt->error,
                );
            }

            //儲存數據 Event_img
            $stmt->prepare("INSERT INTO Event_img (event_ID, media_ID, `order`) VALUES (?, ?, ?)");
            $tmp_img_order = 0;
            foreach ($data['image']['event-image'] as $image) {
                $stmt->bind_param("ssi", $post_id, $image, $tmp_img_order);
                if (!$stmt->execute()) {
                    return array(
                        'code' => 500,
                        'Title' => 'Database Error! 1',
                        'Message' => $stmt->error,
                    );
                }
                $tmp_img_order++;
            }

            //儲存數據 Event_plan
            $stmt->prepare("INSERT INTO Event_plan (Event_ID, plan_ID, plan_name, price, max_people, max_each_user) VALUES (?, ?, ?, ?, ?, ?)");
            foreach ($data['plan'] as $plan) {
                //轉換數據類型
                $plan['id'] = intval($plan['id']);
                $plan['max'] = intval($plan['max']);
                $plan['max_each'] = intval($plan['max_each']);
                $plan['price'] = floatval($plan['price']);

                $stmt->bind_param("iisdii", $post_id, $plan['id'], $plan['name'], $plan['price'], $plan['max'], $plan['max_each']);
                if (!$stmt->execute()) {
                    return array(
                        'code' => 500,
                        'Title' => 'Database Error! 2',
                        'Message' => $stmt->error,
                    );
                }
            }

            //儲存數據 Event_schedule
            $stmt->prepare("INSERT INTO Event_schedule (Event_ID, Schedule_ID, type, plan, start_date, end_date, start_time, end_time, repeat_week) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            foreach ($data['schedule'] as $schedule) {
                //轉換數據類型
                $schedule['id'] = intval($schedule['id']);
                $schedule['type'] = intval($schedule['type']);
                $schedule['plan'] = intval($schedule['plan']);
                $schedule['end'] = $schedule['end'] === "" ? null : $schedule['end'];
                $schedule['week'] = $schedule['week'] !== "" ? json_encode($schedule['week']) : null;

                $stmt->bind_param("iiiisssss", $post_id, $schedule['id'], $schedule['type'], $schedule['plan'], $schedule['start'], $schedule['end'], $schedule['time_start'], $schedule['time_end'], $schedule['week']);
                if (!$stmt->execute()) {
                    return array(
                        'code' => 500,
                        'Title' => 'Database Error! 3',
                        'Message' => $stmt->error,
                    );
                }
            }

            return array(
                'code' => 200,
                'Message' => "活動已成功更新!"
            );
        }

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
            <li><a href='/panel/post'>活動</a></li>
            <li><span>增加活動</span></li>";
    }

    public function get_Title(): string {
        return "增加活動 | X-Travel";
    }

    public function get_Head(): string {
        return "增加活動";
    }

    /**
     * 處理data
     * @param array $data raw data
     * @return array processed data
     */
    private function serializeData(array $data): array {
        //截斷過長字串
        $data['data']['event-summary'] = mb_str_split($data['data']['event-summary'], 80)[0];
        $data['data']['event-precautions'] = mb_str_split($data['data']['event-precautions'], 500)[0];
        $data['data']['event-description'] = mb_str_split($data['data']['event-description'], 1000)[0];
        $data['data']['event-tag'] = mb_str_split($data['data']['event-tag'], 100)[0];
        $data['title']['event-title'] = mb_str_split($data['title']['event-title'], 50)[0];
        $data['location']['event-location'] = mb_str_split($data['location']['event-location'], 100)[0];

        //轉換可留空欄位
        $data['data']['event-precautions'] = $data['data']['event-precautions'] === "" ? null : $data['data']['event-precautions'];
        $data['data']['event-precautions-html'] = null;
        $data['attribute']['event-tag'] = $data['attribute']['event-tag'] === "" ? null : $data['attribute']['event-tag'];

        //HTML filter xss config
        $filterXSS_description = HTMLPurifier_Config::createDefault();
        $filterXSS_description->set('HTML.Allowed', "h1,h2,h3,h4,h5,h6,a[href|target],strong,em,del,br,p,ul[class],ol,li,table,thead,th,tbody,td,tr,blockquote,hr,img[src|alt]");
        $filterXSS_precautions = HTMLPurifier_Config::createDefault();
        $filterXSS_precautions->set('HTML.Allowed', "strong,em,del,br,p,ul[class],ol,li");

        //轉換Markdown to html & filter xss
        $MD_converter = new Parsedown_ext();
        $purifier = new HTMLPurifier($filterXSS_description);
        //event-description
        $data['data']['event-description-html'] = str_replace("\n", "", $MD_converter->text($data['data']['event-description']));
        $data['data']['event-description-html'] = $purifier->purify($data['data']['event-description-html']);
        //event-precautions
        if ($data['data']['event-precautions'] !== null) {
            $purifier->config = $filterXSS_precautions;
            $data['data']['event-precautions-html'] = str_replace("\n", "", $MD_converter->text($data['data']['event-precautions']));
            $data['data']['event-precautions-html'] = $purifier->purify($data['data']['event-precautions-html']);
        }

        //轉換image to array
        $data['image']['event-image'] = explode(',', $data['image']['event-image']);

        //轉換發佈日期
        $data['status']['event-post'] = $data['status']['event-post-date'] . ' ' . $data['status']['event-post-time'];

        //轉換數據類型
        $data['status']['event-status'] = intval($data['status']['event-status']);
        $data['attribute']['event-type'] = intval($data['attribute']['event-type']);
        $data['location']['event-longitude'] = floatval($data['location']['event-longitude']);
        $data['location']['event-latitude'] = floatval($data['location']['event-latitude']);

        return $data;
    }
}