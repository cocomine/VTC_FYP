<?php
/*
 * Copyright (c) 2023.
 * Create by cocomine
 */

namespace panel\page;

use cocomine\IPage;
use mysqli;

class event implements IPage {

    private mysqli $sqlcon;

    public function __construct(mysqli $conn, array $upPath) {
        $this->sqlcon = $conn;
    }
    /**
     * @inheritDoc
     */
    public function access(bool $isAuth, int $role, bool $isPost): int {
        if (!$isAuth) return 401;
        if ($role < 2) return 403;
        return 200;
    }

    /**
     * @inheritDoc
     */
    public function showPage(): string {
        // TODO: Implement showPage() method.
    }

    /**
     * @inheritDoc
     */
    public function post(array $data): array {
        return array();
    }

    /**
     * @inheritDoc
     */
    public function path(): string {
        return "<li><a href='/panel'>" . showText("index.home") . "</a></li>
            <li><span>>活動</span></li>";
    }

    /**
     * @inheritDoc
     */
    public function get_Title(): string {
        return "活動 | X-Travel";
    }

    /**
     * @inheritDoc
     */
    public function get_Head(): string {
        return "活動";
    }
}