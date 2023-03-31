<?php
/*
 * Copyright (c) 2023.
 * Create by cocomine
 */

namespace apis\app;

use mysqli;

class xmap implements \cocomine\IApi {

    private mysqli $sqlcon;

    /**
     * @param mysqli $sqlcon
     */
    public function __construct(mysqli $sqlcon) { $this->sqlcon = $sqlcon; }


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
        http_response_code(403);
    }

    /**
     * @inheritDoc
     */
    public function post(?array $data) {
        header("Content-Type: text/json");
        $stmt = $this->sqlcon->prepare("SELECT ID, name, summary, thumbnail, longitude, latitude FROM Event WHERE longitude BETWEEN ? AND ? AND latitude BETWEEN ? AND ? AND state = 1 ORDER BY create_time DESC");
        $stmt->bind_param("dddd", $data['northEast']['longitude'], $data['southWest']['longitude'], $data['northEast']['latitude'], $data['southWest']['latitude']);
        if (!$stmt->execute()) {
            http_response_code(500);
            echo json_encode(['status' => 500, 'Message' => $stmt->error, 'Title' => showText('Error_Page.something_happened')]);
        }

        $result = $stmt->get_result();
        echo json_encode(['code' => 200, 'data' => $result->fetch_all(MYSQLI_ASSOC)]);
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