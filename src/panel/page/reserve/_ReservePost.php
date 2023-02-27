<?php

namespace panel\page\reserve;

use mysqli;

class _ReservePost implements \cocomine\IPage {

    private mysqli $sqlcon;
    private array $upPath;

    public function __construct(mysqli $conn, array $upPath) {
        $this->sqlcon = $conn;
        $this->upPath = $upPath;
    }

    /**
     * @inheritDoc
     */
    public function access(bool $isAuth, int $role, bool $isPost): int {
        global $auth;

        if (!$isAuth) return 401;
        if ($role < 2) return 403;

        //check the event is true owner
        if (sizeof($this->upPath) > 0 && preg_match("/[0-9]+/", $this->upPath[0])) {
            $stmt = $this->sqlcon->prepare("SELECT COUNT(ID) AS 'count' FROM Event WHERE ID = ? AND UUID = ?");
            $stmt->bind_param('ss', $this->upPath[0], $auth->userdata['UUID']);
            if (!$stmt->execute()) return 500;

            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            if ($row['count'] <= 0) return 403;
        }
        return 200;
    }

    /**
     * @inheritDoc
     */
    public function showPage(): string {
        return <<<body

body;

    }

    /**
     * @inheritDoc
     */
    public function post(array $data): array {
        return $data;
    }

    /**
     * @inheritDoc
     */
    public function path(): string {
        return "<li><a href='/panel'>" . showText("index.home") . "</a></li>
            <li><span>審核活動</span></li>";
    }

    /**
     * @inheritDoc
     */
    public function get_Title(): string {
        return "審核活動 | X-Travel";
    }

    /**
     * @inheritDoc
     */
    public function get_Head(): string {
        return "審核活動";
    }
}