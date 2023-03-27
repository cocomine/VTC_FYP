<?php
/*
 * Copyright (c) 2022.
 * Create by cocomine
 */

namespace panel\apis;

use cocomine\IApi;
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
     * 產生圖片lazy load html element
     * @param string $imgID 圖片ID
     * @return string $Img html element
     */
    public static function Generate_img_html(string $imgID): string {
        return "<img src='/panel/assets/images/image_loading.webp' draggable='false' alt='$imgID Image' data-src='/panel/api/media/$imgID' class='lazy'/>";
    }

    /**
     * @inheritDoc
     */
    public function access(bool $isAuth, int $role): int {
        return 200;
    }

    /**
     * 檢索媒體
     * @inheritDoc
     */
    public function get() {
        if (sizeof($this->upPath) < 1) {
            echo_error(400);
            return;
        }

        if ($this->upPath[0] === 'list') {
            /* 列出所有媒體 */
            global $auth;
            header("content-type: text/json; charset=utf-8");

            /* 檢查權限 */
            if (!$auth->islogin) {
                echo_error(401);
                return;
            }
            if ($auth->userdata['Role'] <= 1) {
                echo_error(403);
                return;
            }

            /* 取得所有媒體 */
            $stmt = $this->sqlcon->prepare('SELECT ID, MIME, Datetime, name FROM media WHERE User = ? ORDER BY Datetime DESC');
            $stmt->bind_param('s', $auth->userdata['UUID']);
            if (!$stmt->execute()) {
                echo_error(500);
                return;
            }

            /* 取得result */
            $result = $stmt->get_result();
            $data = [];
            while ($row = $result->fetch_assoc()) {
                $data[] = array(
                    'id' => $row['ID'],
                    'mime' => $row['MIME'],
                    'datetime' => $row['Datetime'],
                    'name' => $row['name']
                );
            }

            /* 輸出 */
            echo json_encode(array(
                'code' => 200,
                'body' => $data
            ));
        } else {
            /* 展示媒體 */
            $stmt = $this->sqlcon->prepare('SELECT path, MIME FROM media WHERE ID = ?');
            $stmt->bind_param('s', $this->upPath[0]);
            if (!$stmt->execute()) {
                echo_error(500);
                return;
            }

            /* 取得result */
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            if ($result->num_rows <= 0) {
                http_response_code(404);
                header("Content-type: image/webp");
                readfile('./assets/images/image_not_found.webp');
                return;
            }

            /* 檔案不存在 */
            if(!file_exists($row['path'])){
                http_response_code(404);
                header("Content-type: image/webp");
                readfile('./assets/images/image_not_found.webp');
                return;
            }

            /* 展示圖片 */
            header("Content-type: " . $row['MIME']);
            readfile($row['path']);
        }
    }

    /**
     * 上載媒體
     * @inheritDoc
     */
    public function post(?array $data) {
        global $auth;
        header("content-type: text/json; charset=utf-8");

        /* 檢查權限 */
        if (!$auth->islogin) {
            echo_error(401);
            return;
        }
        if ($auth->userdata['Role'] < 1) {
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
                    $output = array('code' => 400, 'Error' => 'UPLOAD_ERR_INI_SIZE', 'Message' => showText("Media-upload.Content.respond.Over_size"));
                    break;
                case UPLOAD_ERR_FORM_SIZE:
                    $output = array('code' => 400, 'Error' => 'UPLOAD_ERR_FORM_SIZE', 'Message' => showText("Media-upload.Content.respond.Over_size"));
                    break;
                case  UPLOAD_ERR_PARTIAL:
                    $output = array('code' => 400, 'Error' => 'UPLOAD_ERR_PARTIAL', 'Message' => showText("Media-upload.Content.respond.Not_complete"));
                    break;
                case  UPLOAD_ERR_NO_FILE:
                    $output = array('code' => 400, 'Error' => 'UPLOAD_ERR_NO_FILE', 'Message' => showText("Media-upload.Content.respond.Not_complete_upload"));
                    break;
                default:
                    $output = array('code' => 400, 'Error' => $_FILES["file"]["error"], 'Message' => showText("Error"));
                    break;
            }
            echo json_encode($output);
            return;
        }

        /* 判斷限制 */
        //檢查文件類型
        if (!preg_match('/(image\/jpeg)|(image\/png)|(image\/webp)|(image\/gif)|(application\/pdf)/', $mime)) {
            http_response_code(400);
            echo json_encode(array('code' => 400, 'Error' => 'UPLOAD_ERR_FILE_TYPE', 'Message' => showText("Media-upload.Content.respond.File_type_not_mach")));
            return;
        }

        //8MB 限制
        if ($_FILES["file"]["size"] > 8388608) {
            http_response_code(400);
            echo json_encode(array('code' => 400, 'Error' => 'UPLOAD_ERR_FORM_SIZE', 'Message' => showText("Media-upload.Content.respond.Over_size")));
            return;
        }

        //檔案名稱20個字或以下
        $path = pathinfo($_FILES["file"]["name"]);
        $name = filter_var($path['filename'], FILTER_SANITIZE_STRING);
        if (strlen($name) > 20) {
            http_response_code(400);
            echo json_encode(array('code' => 400, 'Error' => 'UPLOAD_ERR_NAME_SIZE', 'Message' => showText("Media-upload.Content.respond.File_name_over")));
            return;
        }

        $nowYear = date('Y');
        $nowMonth = date('m');
        // 創建文件夾
        if (!file_exists("./upload/" . $nowYear . '/' . $nowMonth)) {
            mkdir("./upload/" . $nowYear . '/' . $nowMonth, 0644, true);
        }

        /* 轉換圖片 */
        if (preg_match('/(image\/.+)/', $mime)) {
            try {
                $imgk = new Imagick($_FILES["file"]["tmp_name"]);
                $imgk->setBackgroundColor('white');
                $imgk->setImageFormat('WEBP');
                $imgk->setCompressionQuality(80);
                $imgk->setOption('webp:lossless', true);
                $blob = $imgk->getImageBlob();
                $mime = "image/webp";
            } catch (ImagickException $e) {
                http_response_code(500);
                echo json_encode(array('code' => 500, 'Error' => 'UPLOAD_ERR_CONVERT', 'Message' => showText("Media-upload.Content.respond.File_convert_fail")));
                return;
            }
        }

        /* 組合儲存路徑 */
        if ($mime === "image/webp") {
            //webp格式
            $extension = '.webp';
            $Scan = true;
        } else {
            //其他格式
            $extension = '.' . filter_var($path['extension'], FILTER_SANITIZE_STRING);
            $Scan = false;
        }
        $save_path = ["./upload/" . $nowYear . '/' . $nowMonth . "/", $name, $extension]; // [文件夾, 檔案名稱, 副檔名]

        /* 添加資料到數據庫 */
        $try = 10;
        while (true) {
            $save_path[1] = Generate_Code(6);
            $tmp = join('', $save_path);

            $stmt = $this->sqlcon->prepare("INSERT INTO media (ID, path, User, MIME, Scan, name) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssis", $save_path[1], $tmp, $auth->userdata['UUID'], $mime, $Scan, $name);
            $try--;
            if ($stmt->execute()) break;
            if ($try <= 0) {
                http_response_code(500);
                echo json_encode(array('code' => 500, 'Error' => 'UPLOAD_ERR_SQL', 'Message' => showText("Media-upload.Content.respond.File_sql_fail")));
                return;
            }
        }

        /* 儲存文件 */
        if ($mime === "image/webp") {
            if (!file_put_contents(join('', $save_path), $blob)) {
                http_response_code(500);
                echo json_encode(array('code' => 500, 'Error' => 'UPLOAD_ERR_SAVE', 'Message' => showText("Media-upload.Content.respond.File_save_fail")));
                return;
            }
        } else {
            if (!move_uploaded_file($_FILES["file"]["tmp_name"], join('', $save_path))) {
                http_response_code(500);
                echo json_encode(array('code' => 500, 'Error' => 'UPLOAD_ERR_SAVE', 'Message' => showText("Media-upload.Content.respond.File_save_fail")));
                return;
            }
        }

        /* 成功 */
        http_response_code(201);
        echo json_encode(array('code' => 201, 'Success' => 'UPLOAD_ERR_OK', 'Message' => showText("Media-upload.Content.respond.Uploaded"), 'body' => $save_path[1]));
    }

    /**
     * 更新檔案名稱
     * @inheritDoc
     */
    public function put(?array $data) {
        global $auth;

        if (sizeof($this->upPath) < 1) {
            echo_error(400);
            return;
        }

        /* 檢查權限 */
        if (!$auth->islogin) {
            echo_error(401);
            return;
        }
        if ($auth->userdata['Role'] < 2 || $data === null) {
            echo_error(403);
            return;
        }

        /* 修改檔案名稱 */
        $stmt = $this->sqlcon->prepare('UPDATE media SET name = ? WHERE ID = ? AND User = ?');
        $stmt->bind_param('sss', $data['name'] ,$this->upPath[0], $auth->userdata['UUID']);
        if (!$stmt->execute()) {
            echo_error(500);
            return;
        }

        /* 檢查刪除結果 */
        if ($stmt->affected_rows >= 1) {
            echo json_encode(array('code' => 200, 'Message' => showText('Media.Content.respond.edit.success')));
        }else{
            http_response_code(400);
            echo json_encode(array('code' => 400, 'Message' => showText('Media.Content.respond.edit.fail')));
        }
    }

    /**
     * @inheritDoc
     */
    public function delete(?array $data) {
        global $auth;
        header("content-type: text/json; charset=utf-8");

        /* 檢查權限 */
        if (!$auth->islogin) {
            echo_error(401);
            return;
        }
        if ($auth->userdata['Role'] < 2) {
            echo_error(403);
            return;
        }

        /* 檢查請求內容 */
        if (sizeof($this->upPath) === 1 && $data === null) {
            /* 刪除單個 */
            $stmt = $this->sqlcon->prepare('DELETE FROM media WHERE ID = ? AND User = ?');
            $stmt->bind_param('ss', $this->upPath[0], $auth->userdata['UUID']);
            if (!$stmt->execute()) {
                echo_error(500);
                return;
            }

            /* 檢查刪除結果 */
            if ($stmt->affected_rows >= 1) {
                echo json_encode(array('code' => 200, 'Message' => showText('Media.Content.respond.single.success')));
            }else{
                http_response_code(400);
                echo json_encode(array('code' => 400, 'Message' => showText('Media.Content.respond.single.fail')));
            }

        } else if (sizeof($this->upPath) < 1) {
            /* 批量刪除 */
            $stmt = $this->sqlcon->prepare('DELETE FROM media WHERE ID = ? AND User = ?');
            $deleted_img = [];

            /* 刪除 */
            foreach ($data as $id) {
                $stmt->bind_param('ss', $id, $auth->userdata['UUID']);
                if (!$stmt->execute()) {
                    echo_error(500);
                    return;
                }
                if ($stmt->affected_rows >= 1) $deleted_img[] = $id;
            }

            /* 檢查刪除結果 */
            if (sizeof($deleted_img) === sizeof($data)) {
                echo json_encode(array(
                    'code' => 200,
                    'Message' => showText('Media.Content.respond.multi.success'),
                    'body' => $deleted_img
                ));
            } else if (sizeof($deleted_img) > 0) {
                http_response_code(210);
                echo json_encode(array(
                    'code' => 210,
                    'Message' => showText('Media.Content.respond.multi.partially'),
                    'body' => $deleted_img
                ));
            } else {
                http_response_code(400);
                echo json_encode(array(
                    'code' => 400,
                    'Message' => showText('Media.Content.respond.multi.fail'),
                    'body' => $deleted_img
                ));
            }
        } else {
            echo_error(400);
        }
    }
}