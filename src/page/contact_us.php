<?php

namespace panel\page;

class contact_us implements IPage {
    private mysqli $sqlcon;

    /**
     * @inheritDoc
     */

    public function access(bool $isAuth, int $role, bool $isPost): int
    {
        return 200;
    }

    /**
     * @inheritDoc
     */
    public function showPage(): string
    {
        // TODO: Implement showPage() method.
    }

    /**
     * @inheritDoc
     */
    public function post(array $data): array
    {
        global $auth;

        return array();
    }

    /**
     * @inheritDoc
     */
    public function path(): string
    {
        return '<li class="breadcrumb-item active">'.showText("index.contact_us").'</li>';
    }

    /**
     * @inheritDoc
     */
    public function get_Title(): string
    {
        return showText('index.title');
    }

    /**
     * @inheritDoc
     */
    public function get_Head(): string
    {
        return showText("index.contact_us");
    }
}