<?php
/*
 * Copyright (c) 2022.
 * Create by cocomine
 */

namespace panel\page;

use cocomine\IPage;

class media implements IPage {

    /**
     * @inheritDoc
     */
    public function access(bool $isAuth, int $role, bool $isPost): int {
        if(!$isAuth) return 401;
        if($role < 2) return 403;
        return 200;
    }

    /**
     * @inheritDoc
     */
    public function showPage(): string {

        $Text = showText('Media.Content');
        $LangJson = json_encode(array(
            'Select_on'=>$Text['Select_on'],
            'Select_off'=>$Text['Select_off'],
            'No_media'=>$Text['No_media'],
            'Media'=>$Text['Media'].' %s',
        ));

        return <<<body
<pre id="LangJson" class="d-none">$LangJson</pre>
<div class='col-12 mt-4'>
    <div class='card'>
        <div class='card-body'>
            <div class="row justify-content-between">
                <div class="col-auto">
                    <button type="button" class="btn btn-outline-primary btn-rounded" id="switch-mode">{$Text['Select_on']}</button>
                </div>
                <div class="col-auto">
                    <button type="button" class="btn btn-danger btn-rounded" id="del-media" style="display: none"><i class="fa-solid fa-trash me-2"></i>{$Text['Delete'][0]} <span>0</span> {$Text['Delete'][1]}</button>
                </div>
            </div>
        </div>
    </div>
</div>
<div class='col-12 mt-4'>
    <div class='card'>
        <div class='card-body'>
            <div class='row gy-4 align-items-center media-list' id="media-list"></div>
        </div>
    </div>
</div>
<div id='Media-modal' class='modal fade' tabindex='-1'>
    <div class='modal-dialog modal-xl'>
        <div class='modal-content'>
            <div class='modal-header'>
                <h5 class='modal-title'><b>{$Text['Media']} <span>xxxxxx</span></b></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class='modal-body'>
                <div class="row">
                    <div class="col text-center">
                        <img src='/panel/assets/images/image_loading.webp' draggable='false' alt='xxxx' style="max-height: 70vh"/>
                    </div>
                    <div class="col-12 col-md-3" id="Media-modal-detail">
                        <p>{$Text['Media_ID']} <span>xxxx</span></p>
                        <p>{$Text['Upload_Time']} <span>xxxx</span></p>
                        <p>{$Text['MIME_type']} <span>xxxx</span></p>
                        <p>{$Text['URL']} <code class="bg-light">xxxx</code></p>
                        <a target="_blank" class="btn btn-outline-primary btn-rounded" href="./"><i class="fa-solid fa-arrow-up-right-from-square me-2"></i>{$Text['Show_Original']}</a>
                        <button type="button" data-id="xxx" class="btn btn-danger btn-rounded"><i class="fa-solid fa-trash me-2"></i>{$Text['Delete'][0]} {$Text['Delete'][1]}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<style>
.media-list .media-list-center{
    cursor: pointer;
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
.media-list.select-mode .media-list-focus{
    opacity: .6;
}
.media-list .media-list-focus.selected{
    border: #12a0ff 5px solid;
    opacity: 1;
}
.media-list .media-list-focus{
    opacity: 1;
    position: relative;
    border: #12a0ff 0 solid;
    transition: all 100ms ease-in-out;
}
.media-list .media-list-focus:hover{
    box-shadow: 0 0 5px 6px rgba(var(--bs-primary-rgb), 0.5);
}
</style>
<script>
    loadModules(['myself/page/media/media'])
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
        return "<li><a href='/panel'>" . showText("index.home") . "</a></li>
            <li><span>" . showText("Media.Head") . "</span></li>";
    }

    /**
     * @inheritDoc
     */
    public function get_Title(): string {
        return showText("Media.Title");
    }

    /**
     * @inheritDoc
     */
    public function get_Head(): string {
        return showText("Media.Head");
    }
}