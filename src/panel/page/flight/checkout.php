<?php
/*
 * Copyright (c) 2022.
 * Create by cocomine
 */

namespace panel\page\flight;

use cocomine\IPage;
use mysqli;

class checkout implements IPage {

    private mysqli $sqlcon;
    private array $upPath;
    private string $flight;

    /**
     * @param mysqli $conn
     * @param array $upPath
     */
    public function __construct(mysqli $conn, array $upPath) {
        $this->sqlcon = $conn;
        $this->upPath = $upPath;
    }

    /**
     * @inheritDoc
     */
    public function access(bool $isAuth, int $role): int {
        if(sizeof($this->upPath) != 1) return 404;

        /* 是否在本日之後 */
        $stmt = $this->sqlcon->prepare("SELECT Flight FROM Flight WHERE ID = ? AND DateTime >= CURRENT_DATE");
        $stmt->bind_param('s', $this->upPath[0]);
        if(!$stmt->execute()) return 500;

        $result = $stmt->get_result();
        if(!$result->num_rows > 0) return 404; //不是本日期之後
        $this->flight = $result->fetch_assoc()['Flight'];

        if($isAuth && $role >= 1) return 200;
        return 403;
    }

    /**
     * @inheritDoc
     */
    public function showPage(): string {
        return "";
    }

    /**
     * @inheritDoc
     */
    function post(array $data): array {
        return array();
    }

    /**
     * @inheritDoc
     */
    function path(): string {
        return "";
    }

    /**
     * @inheritDoc
     */
    public function get_Title(): string {
        return "";
    }

    /**
     * @inheritDoc
     */
    public function get_Head(): string {
        return "";
    }
}