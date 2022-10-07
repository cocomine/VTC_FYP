<?php
/*
 * Copyright (c) 2020.
 * Create by cocomine
 */

namespace cocomine\API;

use mysqli;

/**
 * Class notifyAPI
 * @package cocopixelmc\API
 */
class notifyAPI
{

    private $sqlcon         = null;
    static $Status_Primary  = "btn-primary";
    static $Status_Success  = "btn-success";
    static $Status_Danger   = "btn-danger";
    static $Status_Warning  = "btn-warning";
    static $Status_Info     = "btn-info";

    /**
     * notifyAPI constructor.
     * @param mysqli $sqlcon SQL連接
     */
    public function __construct(mysqli $sqlcon){
        $this->sqlcon = $sqlcon;
    }

    /**
     * 取得通知
     * @param string $uuid 用戶id
     * @param int $limit 條目限制
     * @return false|array 資料或狀態
     */
    public function Show_notify(string $uuid, int $limit = 0){

        $query = "SELECT * FROM notify WHERE UUID = ? ORDER BY Time DESC ";
        if($limit > 0) $query .= " LIMIT ".$limit;

        $stmt = $this->sqlcon->prepare($query);
        $stmt->bind_param('s', $uuid);
        if(!$stmt -> execute()){
            return false;
        }

        $result = $stmt -> get_result();
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
    public function Send_notify(string $uuid, string $icon, string $status, string $link, string $Msg){
        $icon = sprintf("<i class='%s %s'></i>", $icon, $status);
        $stmt = $this->sqlcon->prepare("INSERT INTO notify (UUID, Time, icon, link, Msg) VALUES (?, UNIX_TIMESTAMP(), ?, ?, ?)");
        $stmt->bind_param('ssss', $uuid, $icon, $link, $Msg);
        if(!$stmt -> execute()){
            return false;
        }else{
            return true;
        }
    }

    /**
     * 刪除通知
     * @param int $id 通知id
     * @return bool 是否成功
     */
    public function Delete_notify(int $id){
        $stmt = $this->sqlcon->prepare("DELETE FROM notify WHERE notifyID = ?");
        $stmt->bind_param('i', $id);
        if(!$stmt -> execute()){
            return false;
        }
        return true;
    }
}