<?php
/*
 * Copyright (c) 2023.
 * Create by cocomine
 */

namespace page;

class privacy implements \cocomine\IPage {

    /**
     * @inheritDoc
     */
    public function access(bool $isAuth, int $role, bool $isPost): int {
        return 200;
    }

    /**
     * @inheritDoc
     */
    public function showPage(): string {
        $content = showText('privacy.content.content');

        return <<<HTML
<div class="container mt-4">
    <div class="card">
        <div class="card-body">
            <div class="card-text">$content</div>
        </div>
    </div>
</div>
HTML;
    }

    /**
     * @inheritDoc
     */
    public function post(array $data): array {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function path(): string {
        return '<li class="breadcrumb-item"><a href="/">' . showText("index.home") . '</a></li>'
            . '<li class="breadcrumb-item active">' . showText('privacy.Head') . '</li>';
    }

    /**
     * @inheritDoc
     */
    public function get_Title(): string {
        return showText('privacy.title');
    }

    /**
     * @inheritDoc
     */
    public function get_Head(): string {
        return showText('privacy.Head');
    }

    /**
     * @inheritDoc
     */
    public function get_description(): ?string {
        return showText('privacy.description');
    }

    /**
     * @inheritDoc
     */
    public function get_image(): ?string {
        return null;
    }
}