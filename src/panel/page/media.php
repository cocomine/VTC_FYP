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

        return <<<body

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