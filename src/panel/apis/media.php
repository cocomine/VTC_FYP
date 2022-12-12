<?php
/*
 * Copyright (c) 2022.
 * Create by cocomine
 */

namespace panel\apis;

use cocomine\IApi;
use finfo;
use Imagick;
use ImagickException;
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
        return 200;
    }

    /**
     * @inheritDoc
     */
    public function get() {
        if (sizeof($this->upPath) < 1) {
            echo_error(400);
            return;
        }
        //todo: show img
    }

    /**
     * 上載媒體
     * @inheritDoc
     */
    public function post($data) {
        global $auth;
        header("content-type: text/json; charset=utf-8");

        /* 檢查權限 */
        if(!$auth->islogin){
            echo_error(401);
            return;
        }
        if ($auth->userdata['Role'] <= 1){
            echo_error(403);
            return;
        }

        /* 檢查請求內容 */
        if ($data !== null || $_FILES["file"] === null) {
            echo_error(400);
            return;
        }

        /* 取得檔案類型 */
        $fin = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($fin, $_FILES["file"]['tmp_name']);

        /* 判斷成功與否 */
        if ($_FILES["file"]["error"] > UPLOAD_ERR_OK) {
            http_response_code(400);
            switch ($_FILES["file"]["error"]) {
                case UPLOAD_ERR_INI_SIZE:
                    $output = array('code' => 400, 'Error' => 'UPLOAD_ERR_INI_SIZE', 'Message' => showText("Media-upload.response.Over_size"));
                    break;
                case UPLOAD_ERR_FORM_SIZE:
                    $output = array('code' => 400, 'Error' => 'UPLOAD_ERR_FORM_SIZE', 'Message' => showText("Media-upload.response.Over_size"));
                    break;
                case  UPLOAD_ERR_PARTIAL:
                    $output = array('code' => 400, 'Error' => 'UPLOAD_ERR_PARTIAL', 'Message' => showText("Media-upload.response.Not_complete"));
                    break;
                case  UPLOAD_ERR_NO_FILE:
                    $output = array('code' => 400, 'Error' => 'UPLOAD_ERR_NO_FILE', 'Message' => showText("Media-upload.response.Not_complete_upload"));
                    break;
                default:
                    $output = array('code' => 400, 'Error' => $_FILES["file"]["error"], 'Message' => showText("Media-upload.response.Unknown_Error"));
                    break;
            }
            echo json_encode($output);
            return;
        }

        /* 判斷限制 */
        //檢查文件類型
        if (!preg_match('/(image\/jpeg)|(image\/png)|(image\/webp)|(image\/gif)/', $mime)) {
            http_response_code(400);
            echo json_encode(array('code' => 400, 'Error' => 'UPLOAD_ERR_FILE_TYPE', 'Message' => showText("Media-upload.response.File_type_not_mach")));
            return;
        }

        //8MB 限制
        if ($_FILES["file"]["size"] > 8388608) {
            http_response_code(400);
            echo json_encode(array('code' => 400, 'Error' => 'UPLOAD_ERR_FORM_SIZE', 'Message' => showText("Media-upload.response.Over_size")));
            return;
        }

        //檔案名稱20個字或以下
        if (strlen($_FILES["file"]["name"]) > 20) {
            http_response_code(400);
            echo json_encode(array('code' => 400, 'Error' => 'UPLOAD_ERR_NAME_SIZE', 'Message' => showText("Media-upload.response.File_name_over")));
            return;
        }

        $nowYear = date('Y');
        $nowMonth = date('m');
        // 創建文件夾
        if (!file_exists("./upload/" . $nowYear . '/' . $nowMonth)) {
            mkdir("./upload/" . $nowYear . '/' . $nowMonth, 0755, true);
        }

        /* 轉換圖片 */
        try {
            $imgk = new Imagick($_FILES["file"]["tmp_name"]);
            $imgk->setImageFormat('WEBP');
            $imgk->setCompressionQuality(80);
            $imgk->setOption('webp:lossless', true);
            $blob = $imgk->getImageBlob();
        } catch (ImagickException $e) {
            http_response_code(500);
            echo json_encode(array('code' => 500, 'Error' => 'UPLOAD_ERR_CONVERT', 'Message' => showText("Media-upload.response.File_convert_fail")));
            return;
        }

        /* 添加資料到數據庫 */
        //todo:add to sql
        $try = 10;
        while (true) {
            $code = Generate_Code(6);
            $save_path = "./upload/" . $nowYear . '/' . $nowMonth . "/" . $code . '.webp';
            $stmt = $this->sqlcon->prepare("INSERT INTO media (ID, path, User) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $code, $save_path, $auth->userdata['UUID']);
            $try--;
            if ($stmt->execute()) break;
            if($try <= 0){
                http_response_code(500);
                echo json_encode(array('code' => 500, 'Error' => 'UPLOAD_ERR_SQL', 'Message' => showText("Media-upload.response.File_sql_fail")));
                return;
            }
        }

        /* 儲存文件 */
        if (!file_put_contents($save_path, $blob)) {
            http_response_code(500);
            echo json_encode(array('code' => 500, 'Error' => 'UPLOAD_ERR_SAVE', 'Message' => showText("Media-upload.response.File_save_fail")));
            return;
        }

        /* 成功 */
        http_response_code(201);
        echo json_encode(array('code' => 201, 'Success' => 'UPLOAD_ERR_OK', 'Message' => showText("Media-upload.response.Uploaded")));
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
        if (sizeof($this->upPath) < 1) {
            echo_error(400);
            return;
        }
        //todo: delete img
    }
}