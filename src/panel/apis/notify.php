<?php
/*
 * Copyright (c) 2022.
 * Create by cocomine
 */

namespace panel\apis;

use cocomine\IApi;
use Exception;
use mysqli;

/**
 * Class notifyAPI
 * @package cocopixelmc\API
 */
class notify implements IApi {

    private mysqli $sqlcon;
    private array $upPath;

    static string $Status_Primary = "btn-primary";
    static string $Status_Success = "btn-success";
    static string $Status_Danger = "btn-danger";
    static string $Status_Warning = "btn-warning";
    static string $Status_Info = "btn-info";

    /**
     * notifyAPI constructor.
     * @param mysqli $sqlcon SQL連接
     */
    public function __construct(mysqli $sqlcon, array $upPath = array()) {
        $this->sqlcon = $sqlcon;
        $this->upPath = $upPath;
    }

    /**
     * 取得通知
     * @param string $uuid 用戶id
     * @param int $limit 條目限制
     * @return array 資料或狀態
     * @throws Exception Server error
     */
    public function Show_notify(string $uuid, int $limit = 0): array {
        $query = "SELECT * FROM notify WHERE UUID = ? ORDER BY Time DESC ";
        if ($limit > 0) $query .= " LIMIT " . $limit;

        $stmt = $this->sqlcon->prepare($query);
        $stmt->bind_param('s', $uuid);
        if (!$stmt->execute()) throw new Exception('SQL Error');

        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * 傳送通知
     * @param string $uuid 用戶id
     * @param string $icon 通知圖標
     * @param string $status 通知警示狀態
     * @param string $link 通知連結
     * @param string $Msg 通知訊息
     * @return bool 是否成功
     */
    public function Send_notify(string $uuid, string $icon, string $status, string $link, string $Msg): bool {
        $icon = sprintf("<i class='btn %s %s'></i>", $icon, $status);
        $stmt = $this->sqlcon->prepare("INSERT INTO notify (UUID, icon, link, Msg) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('ssss', $uuid, $icon, $link, $Msg);
        if (!$stmt->execute()) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * 刪除通知
     * @param int $id 通知id
     * @return bool 是否成功
     */
    public function Delete_notify(int $id): bool {
        $stmt = $this->sqlcon->prepare("DELETE FROM notify WHERE notifyID = ?");
        $stmt->bind_param('i', $id);
        if (!$stmt->execute()) {
            return false;
        }
        return true;
    }

    public function access(bool $isAuth, int $role): int {
        if (sizeof($this->upPath) > 0) return 403;
        if ($isAuth) return 200;
        return 401;
    }

    public function get() {
        header("content-type: text/json; charset=utf-8");
        global $auth;

        // get notify
        try {
            $result = $this->Show_notify($auth->userdata['UUID'], 20);
            echo json_encode(array(
                'code' => 200,
                'body' => $result
            ));
        } catch (Exception $e) {
            echo_error(500);
        }
    }

    public function post(array $data) {
        http_response_code(204);
    }

    public function put(array $data) {
        http_response_code(204);
    }

    public function delete(array $data) {
        http_response_code(204);
    }
}