<?php
/*
 * Copyright (c) 2022.
 * Create by cocomine
 */

namespace panel\page;

use cocomine\IPage;

class reserve implements IPage {

    /**
     * @inheritDoc
     */
    public function access(bool $isAuth, int $role, bool $isPost): int {
        // TODO: Implement access() method.
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
    function post(array $data): array {
        // TODO: Implement post() method.
    }

    /**
     * @inheritDoc
     */
    function path(): string {
        // TODO: Implement path() method.
    }

    /**
     * @inheritDoc
     */
    public function get_Title(): string {
        // TODO: Implement get_Title() method.
    }

    /**
     * @inheritDoc
     */
    public function get_Head(): string {
        // TODO: Implement get_Head() method.
    }
}