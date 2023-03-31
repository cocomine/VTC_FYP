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
        $stmt->bind_param("dddd", $data['southWest']['longitude'], $data['northEast']['longitude'], $data['southWest']['latitude'], $data['northEast']['latitude']);
        if (!$stmt->execute()) {
            http_response_code(500);
            echo json_encode(['status' => 500, 'Message' => $stmt->error, 'Title' => showText('Error_Page.something_happened')]);
        }

        $data = [];
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $stmt->prepare("SELECT ROUND(SUM(r.rate)/COUNT(*), 1) AS 'rate', COUNT(*) AS 'total' FROM Book_review r, Book_event b WHERE r.Book_ID = b.ID AND event_ID = ?");
            $stmt->bind_param("i", $row['ID']);
            $stmt->execute();
            $rate = $stmt->get_result()->fetch_assoc();
            $row['rate'] = $rate['rate'] ?? 5;
            $row['total'] = $rate['total'];
            $data[] = $row;
        }


        echo json_encode(['code' => 200, 'data' => $data]);
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