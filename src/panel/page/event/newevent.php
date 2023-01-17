<?php
/*
 * Copyright (c) 2023.
 * Create by cocomine
 */

namespace panel\page\event;

use cocomine\IPage;

class newevent implements IPage {

    public function access(bool $isAuth, int $role, bool $isPost): int {
        if(!$isAuth) return 401;
        if($role < 2) return 403;
        return 200;
    }

    public function showPage(): string {

        $Text = showText('Media.Content');
        $Text2 = showText('Media-upload.Content');

        $LangJson = json_encode(array(
            'No_media'           => $Text['No_media'],
            'Media'              => $Text['Media'] . ' %s',
            'Unknown_Error'      => showText('Error'),
            'title' => $Text['Media_Select']['title'],
            'Select' => $Text['Media_Select']['Select'],
            'upload' => array(
                'Timeout'            => $Text2['respond']['Timeout'],
                'File_name_over'     => $Text2['respond']['File_name_over'],
                'Over_size'          => $Text2['respond']['Over_size'],
                'File_type_not_mach' => $Text2['respond']['File_type_not_mach'],
                'Waiting'            => $Text2['respond']['Waiting'],
                'limit_type' => $Text2['limit_type'],
                'drag' => $Text2['drag']
            )
        ));

        return <<<body
<link rel="stylesheet" href="https://unpkg.com/easymde/dist/easymde.min.css">
<link rel="stylesheet" href="/assets/css/myself/media-select.css">
<pre id="media-select-LangJson" class="d-none">$LangJson</pre>
<div class="col-9">
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
                        <form class="needs-validation" novalidate>
                            <div class="col-12 mb-3">
                                <label for="event-summary" class="form-label">活動摘要</label>
                                <textarea class="form-control" name="event-summary" id="event-summary" rows="2" maxlength="45" required></textarea>
                                <span class="fa-pull-right text-secondary" id="event-summary-count" style="margin-top: -20px; margin-right: 10px">0/45</span>
                                <div class="invalid-feedback">這裏不能留空哦~~</div>
                            </div>
                            <div class="col-12 mb-2">
                                <label for="event-summary" class="form-label">活動注意事項</label>
                                <textarea class="form-control" name="event-precautions" id="event-precautions" rows="4" maxlength="180" required></textarea>
                                <div class="invalid-feedback">這裏不能留空哦~~</div>
                                <textarea id="event-precautions-data" class="d-none" readonly>Hello world</textarea>
                            </div>
                            <div class="col-12">
                                <label for="event-description" class="form-label">活動描述</label>
                                <textarea class="form-control" name="event-description" id="event-description" rows="5" maxlength="950" required></textarea>
                                <div class="invalid-feedback">這裏不能留空哦~~</div>
                                <textarea id="event-description-data" class="d-none" readonly>Hello world</textarea>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- Image select-->
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Event Image</h4>
                    <div class="card-text">
                        <div class="media-list row mb-2" id="image-list"></div>                        </div>
                        <button type="button" class="btn btn-rounded btn-primary" id="image-select">Select Image</button>
                        <small>You can select 5 image.</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="col-3">
    
</div>
<style>
#image-list.media-list{
    flex-wrap: nowrap;
    overflow-x: auto;
}
.media-list .media-list-center{
    cursor: grab;
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    transform: translate(50%,50%);
}
.media-list .media-list-center > img{
    transform: translate(-50%,-50%);
    height: 100%;
    max-width: none;
}
</style>
<script>
    require.config({
        paths:{
            easymde: ['https://unpkg.com/easymde/dist/easymde.min'],
            showdown: ['https://cdn.jsdelivr.net/npm/showdown@2.1.0/dist/showdown.min'],
            xss:['xss.min'],
            'media-select': ['myself/media-select'],
            'media-select.upload': ['myself/media-select.upload'],
        },
        shim: {
            xss: { exports: "filterXSS" },
        }
    })
    loadModules(['myself/page/event/newEvent', 'easymde', 'showdown','xss', 'media-select', 'media-select.upload'])
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