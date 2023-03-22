<?php
/*
 * Copyright (c) 2023.
 * Create by cocomine
 */

namespace apis;

class stripe implements \cocomine\IApi {

    /**
     * @inheritDoc
     */
    public function access(bool $isAuth, int $role): int {
        return 200;
    }

    /**
     * @inheritDoc
     */
    public function get() {
        http_response_code(204);
    }

    /**
     * @inheritDoc
     */
    public function post(array $data) {
        header("Content-Type: text/json");

    }

    /**
     * @inheritDoc
     */
    public function put(array $data) {
        http_response_code(204);
    }

    /**
     * @inheritDoc
     */
    public function delete(array $data) {
        http_response_code(204);
    }
}