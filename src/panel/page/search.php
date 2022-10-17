<?php
/*
 * Copyright (c) 2022.
 * Create by cocomine
 */

namespace panel\page;

use cocomine\IPage;

class search implements IPage {

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
        return "<li><span><a href='/panel'>" . showText("index.home") . "</a></span></li><li><span>search flight</span></li>";
    }

    /**
     * @inheritDoc
     */
    public function get_Title(): string {
        return 'Search flight | IVE airline';
    }

    /**
     * @inheritDoc
     */
    public function get_Head(): string {
        return 'Search flight';
    }
}