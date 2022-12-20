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
        $LangJson = "";

        return <<<body
<pre id="LangJson" class="d-none">$LangJson</pre>
<div class='col-12 mt-4'>
    <div class='card'>
        <div class='card-body'>
            <div class='row gy-4 align-items-center' id="media-list"></div>
        </div>
    </div>
</div>
<style>
.center-img{
    cursor: pointer;
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    transform: translate(50%,50%);
}
.center-img img{
    transform: translate(-50%,-50%);
    height: 100%;
    max-width: none;
}
.img-focus{
    transition: all 300ms ease-in-out;
}
.img-focus:hover{
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