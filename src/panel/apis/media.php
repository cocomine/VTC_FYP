<?php
/*
 * Copyright (c) 2022.
 * Create by cocomine
 */

namespace panel\apis;

use cocomine\IApi;
use mysqli;

class media implements IApi {

    private array $upPath;
    private mysqli $sqlcon;

    /**
     * 媒體處理方式
     * @param array $upPath
     * @param mysqli $sqlcon
     */
    public function __construct(mysqli $sqlcon, array $upPath) {
        $this->upPath = $upPath;
        $this->sqlcon = $sqlcon;
    }

    /**
     * @inheritDoc
     */
    public function access(bool $isAuth, int $role): int {
        if(!$isAuth) return 401;
        if($role <= 1) return 403;
        return 200;
    }

    /**
     * @inheritDoc
     */
    public function get() {
        if(sizeof($this->upPath) < 1){
            echo_error(400);
            return;
        }
        //todo: show img
    }

    /**
     * @inheritDoc
     */
    public function post($data) {
        if($data === null){
            echo_error(400);
            return;
        }
        //todo: save img
    }

    /**
     * @inheritDoc
     */
    public function put(array $data) {
        header("content-type: text/json; charset=utf-8");
        http_response_code(204);
        echo json_encode(array('code' => 204));
    }

    /**
     * @inheritDoc
     */
    public function delete() {
        if(sizeof($this->upPath) < 1){
            echo_error(400);
            return;
        }
        //todo: delete img
    }
}