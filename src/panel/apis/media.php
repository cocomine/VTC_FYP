<?php
/*
 * Copyright (c) 2022.
 * Create by cocomine
 */

namespace panel\apis;

use mysqli;

class media implements \cocomine\IApi {

    private array $upPath;
    private mysqli $sqlcon;

    /**
     * 媒體處理方式
     * @param array $upPath
     * @param mysqli $sqlcon
     */
    public function __construct(array $upPath, mysqli $sqlcon) {
        $this->upPath = $upPath;
        $this->sqlcon = $sqlcon;
    }

    /**
     * @inheritDoc
     */
    public function access(bool $isAuth, int $role): int {
        if(!$isAuth) return 401;
        if($role > 1) return 403;
        return 200;
    }

    /**
     * @inheritDoc
     */
    public function get() {
        // TODO: Implement get() method.
    }

    /**
     * @inheritDoc
     */
    public function post($data) {
        if($data === null){
            echo_error(400);
            return;
        }


    }

    /**
     * @inheritDoc
     */
    public function put(array $data) {
        // TODO: Implement put() method.
    }

    /**
     * @inheritDoc
     */
    public function delete() {
        // TODO: Implement delete() method.
    }
}