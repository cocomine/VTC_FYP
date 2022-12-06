<?php
/*
 * Copyright (c) 2022.
 * Create by cocomine
 */

namespace panel\page;


use cocomine\IPage;
use mysqli;

define('UPLOAD_ERR_FILE_TYPE', 101);
define('UPLOAD_ERR_NAME_SIZE', 102);
define('UPLOAD_ERR_OK_DOUBLE', 100);

class upload implements IPage {
    private mysqli $sqlcon;
    private array $UpPath;

    function __construct(mysqli $sqlcon, array $UpPath) {
        $this->sqlcon = $sqlcon;
        $this->UpPath = $UpPath;
    }

    public function access(bool $isAuth, int $role, bool $isPost): int {
        if(!$isAuth) return 401;
        if($role < 2) return 403;
        return 200;
    }

    public function get_Title(): string {
        return showText("Media-upload.Title");
    }

    public function get_Head(): string {
        return showText("Media-upload.Head");
    }

    /**
     * path輸出
     * @return string 輸出
     */
    function path(): string {
        return "<li><a href='/panel/'>" . showText("index.home") . "</a></li>
                        <li><span>" . showText("Media-upload.Head") . "</span></li>";
    }

    /**
     * 回傳表單資料
     * @return array 彈出窗口
     */
    function post(array $data): array {
        /* 消毒 */
        $_FILES["file"]["name"] = filter_var(trim($_FILES["file"]["name"]), FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
        $_FILES["file"]["type"] = filter_var(trim($_FILES["file"]["type"]), FILTER_SANITIZE_STRING);

        /* 判斷成功與否 */
        if ($_FILES["file"]["error"] > UPLOAD_ERR_OK) {
            switch ($_FILES["file"]["error"]) {
                case UPLOAD_ERR_INI_SIZE:
                    $output = array('Error' => UPLOAD_ERR_INI_SIZE, 'Result' => showText("Media-upload.response.Over_size"));
                    break;
                case UPLOAD_ERR_FORM_SIZE:
                    $output = array('Error' => UPLOAD_ERR_FORM_SIZE, 'Result' => showText("Media-upload.response.Over_size"));
                    break;
                case  UPLOAD_ERR_PARTIAL:
                    $output = array('Error' => UPLOAD_ERR_PARTIAL, 'Result' => showText("Media-upload.response.Not_complete"));
                    break;
                case  UPLOAD_ERR_NO_FILE:
                    $output = array('Error' => UPLOAD_ERR_NO_FILE, 'Result' => showText("Media-upload.response.Not_complete_upload"));
                    break;
                default:
                    $output = array('Error' => $_FILES["file"]["error"], 'Result' => showText("Media-upload.response.Unknown_Error"));
                    break;
            }
            http_response_code(400);
        } else {
            /* 判斷限制 */
            if (preg_match('/(audio\/.+)|(video\/.+)|(image\/.+)/', $_FILES["file"]["type"])) { //檢查文件類型
                if ($_FILES["file"]["size"] <= 8388608) { //8MB 限制
                    if(strlen($_FILES["file"]["name"]) <= 50) { //檔案名稱50字或以下

                        $nowYear = date('Y');
                        if (!file_exists("./upload/" . $nowYear)) {
                            mkdir("./upload/" . $nowYear, 0755, true);
                        }

                        /* 儲存文件 */
                        if (file_exists("./upload/" . $nowYear . "/" . $_FILES["file"]["name"])) {
                            /* 重複儲存 */
                            $files_name = explode(".", $_FILES["file"]["name"]);

                            $time = 1;
                            while (file_exists("./upload/" . $nowYear . "/" . $files_name[0] . ' (' . $time . ').' . $files_name[1])) {
                                $time++;
                            }
                            $save_path = "/upload/" . $nowYear . "/" . $files_name[0] . ' (' . $time . ').' . $files_name[1];
                            move_uploaded_file($_FILES["file"]["tmp_name"], "." . $save_path);
                            $output = array('Success' => UPLOAD_ERR_OK_DOUBLE, 'Result' => showText("Media-upload.response.Double_upload"));
                            http_response_code(201);

                            //in sql
                            $stmt = $this->sqlcon->prepare("INSERT INTO media (ID, FileName, SavePath) VALUES (UUID(), ?, ?)");
                            $stmt->bind_param("ss", $_FILES["file"]["name"], $save_path);
                            if (!$stmt->execute()) {
                                $output = array(
                                    'Error' => '0',
                                    'Result' => 'Database Error!',
                                );
                                http_response_code(400);
                            }
                        } else {
                            /* 儲存 */
                            $save_path = "/upload/" . $nowYear . "/" . $_FILES["file"]["name"];
                            move_uploaded_file($_FILES["file"]["tmp_name"], "." . $save_path);
                            $output = array('Success' => UPLOAD_ERR_OK, 'Result' => showText("Media-upload.response.Uploaded"));
                            http_response_code(201);

                            //in sql
                            /*$stmt = $this->sqlcon->prepare("INSERT INTO media (ID, FileName, SavePath) VALUES (UUID(), ?, ?)");
                            $stmt->bind_param("ss", $_FILES["file"]["name"], $save_path);
                            if (!$stmt->execute()) {
                                $output = array(
                                    'Error' => '0',
                                    'Result' => 'Database Error!',
                                );
                                http_response_code(400);
                            }*/
                        }
                    }else{
                        $output = array('Error' => UPLOAD_ERR_NAME_SIZE, 'Result' => showText("Media-upload.response.File_name_over"));
                        http_response_code(400);
                    }
                } else {
                    $output = array('Error' => UPLOAD_ERR_FORM_SIZE, 'Result' => showText("Media-upload.response.Over_size"));
                    http_response_code(400);
                }
            } else {
                $output = array('Error' => UPLOAD_ERR_FILE_TYPE, 'Result' => showText("Media-upload.response.File_type_not_mach"), 'type' => $_FILES["file"]["type"]);
                http_response_code(400);
            }
        }
        return $output;
    }

    /**
     * 輸出頁面
     * @return string 輸出頁面內容
     */
    function showPage(): string {

        $Text = showText("Media-upload.Content"); //文件翻譯

        return "
        <div class='col-12 mt-4'>
            <div class='card'>
                <div class='card-body'>
                    <h1 class='header-title'>{$Text['upload']}</h1>
                    <div id='drop-area' class='row py-5 justify-content-center'>
                        <h5 class='col-auto'>{$Text['drag']}</h5>
                        <div class='w-100'></div>
                        <p class='col-auto'>{$Text['or']}</p>
                        <div class='w-100'></div>
                        <div class='col-12 col-sm-4 '>
                            <input type='file' class='form-control' id='file-sel' multiple accept='audio/*,video/*,image/*' />
                        </div>
                    </div>
                    <p>{$Text['limit']}</p>
                    <ul class='list-group' id='file-upload-list'></ul>
                </div>
            </div>
        </div>
        <style>
            #drop-area{
                border: 5px dashed #ccc; 
                border-radius: 20px;
                transition: .5s;
            }
        </style>
        <script>
            loadModules(['myself/page/upload'])
        </script>
        ";
    }
}