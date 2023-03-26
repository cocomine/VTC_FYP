<?php
/*
 * Copyright (c) 2023.
 * Create by cocomine
 */

namespace panel\apis;

use mysqli;

class collabora_check implements \cocomine\IApi {

    private mysqli $sqlcon;

    /**
     * @param mysqli $sqlcon
     */
    public function __construct(mysqli $sqlcon) { $this->sqlcon = $sqlcon; }


    /**
     * @inheritDoc
     */
    public function access(bool $isAuth, int $role): int {
        if (!$isAuth) return 401;
        if ($role <= 1) return 403;
        return 200;
    }

    /**
     * 檢查是否已經輸入了組織資料
     * @inheritDoc
     */
    public function get() {
        global $auth;
        header('Content-Type: text/json; charset=utf-8');

        if ($auth->userdata['Role'] >= 2) {
            $stmt = $this->sqlcon->prepare("SELECT COUNT(*) FROM User_detail_collabora WHERE UUID = ?");
            $stmt->bind_param("s", $auth->userdata['UUID']);
            if (!$stmt->execute()) {
                echo_error(500);
                return;
            }

            if ($stmt->get_result()->fetch_row()[0] <= 0) {
                http_response_code(404);
                echo json_encode([
                    'code' => 404,
                    'Message' => '必須填寫組織資料, 才可以繼續使用帳號',
                    'Title' => '填寫組織資料'
                ]);
                return;
            }
        }
        echo json_encode(['code' => 200]);
    }

    /**
     * @inheritDoc
     */
    public function post(?array $data) {
        http_response_code(403);
    }

    /**
     * @inheritDoc
     */
    public function put(?array $data) {
        http_response_code(403);
    }

    /**
     * @inheritDoc
     */
    public function delete(?array $data) {
        http_response_code(403);
    }
}