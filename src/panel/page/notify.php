<?php
/*
 * Copyright (c) 2020.
 * Create by cocomine
 */

namespace panel\page;

use cocomine\IPage;
use mysqli;

/**
 * Class notify
 * @package panel\page\AdminBackground
 */
class notify implements IPage {
    private mysqli $sqlcon;
    private array $UpPath;
    private \panel\apis\notify $notify;

    /**
     * notify constructor.
     * @param mysqli $sqlcon SQL連接
     * @param array $UpPath
     */
    public function __construct(mysqli $sqlcon, array $UpPath){
        $this->sqlcon = $sqlcon;
        $this->UpPath = $UpPath;
        $this->notify = new \panel\apis\notify($sqlcon);
    }

    public function access(bool $isAuth, int $role, bool $isPost): int {
        if(!$isAuth) return 401;
        if($role < 3) return 403;
        return 200;
    }

    public function get_Title(): string {
        return '通知 | Global blacklist';
    }

    public function get_Head(): string {
        return '通知';
    }

    /**
     * 輸出頁面
     * @return string 輸出頁面內容
     */
    public function showPage(): string {

        $user = '';
        $stmt = $this -> sqlcon -> prepare("SELECT UUID, Name, Email FROM User WHERE activated = TRUE");
        if(!$stmt -> execute()){
            $user = "Database Error!";
        }
        $result = $stmt -> get_result();
        while($row = $result -> fetch_assoc()){
            $user .= sprintf("<option value='%s'>%s - %s</option>", $row['UUID'], $row['Name'], $row['Email']);
        }

        return "<!--通知發送-->
                <div class='col-12 mt-4'>
                    <div class='card'>
                        <div class='card-body'>
                            <h1 class='header-title'>通知發送</h1>
                            <form class='needs-validation' novalidate>
                                <div class='col-12'>
                                    <label class='form-label' for='User'>用戶</label>
                                    <select style='font-size: 14px;' class='input-rounded form-select' name='User' id='User' required>
                                        {$user}
                                    </select>
                                </div>
                                <div class='col-12'>
                                    <b class='text-muted mb-3 mt-1 d-block'>警示狀態</b>
                                    <div class='form-check form-check-inline'>
                                        <input type='radio' checked id='primary' name='status' class='form-check-input' value='0'>
                                        <label class='form-check-label text-primary' for='primary'>預設</label>
                                    </div>
                                    <div class='form-check form-check-inline'>
                                        <input type='radio' id='success' name='status' class='form-check-input' value='1'>
                                        <label class='form-check-label text-success' for='success'>成功</label>
                                    </div>
                                    <div class='form-check form-check-inline'>
                                        <input type='radio' id='danger' name='status' class='form-check-input' value='2'>
                                        <label class='form-check-label text-danger' for='danger'>危險</label>
                                    </div>
                                    <div class='form-check form-check-inline'>
                                        <input type='radio' id='warning' name='status' class='form-check-input' value='3'>
                                        <label class='form-check-label text-warning' for='warning'>警告</label>
                                    </div>
                                    <div class='form-check form-check-inline'>
                                        <input type='radio' id='info' name='status' class='form-check-input' value='4'>
                                        <label class='form-check-label text-info' for='info'>信息</label>
                                    </div>
                                </div>
                                <div class='col-12'>
                                    <label for='Icon' class='form-label'>圖標</label>
                                    <input class='form-control input-rounded' type='text' id='Icon' name='Icon' required value='fa fa-exclamation'>
                                    <i id='Icon-show' class='fa-solid fa-exclamation'></i>
                                </div>
                                <div class='col-12'>
                                    <label for='Content' class='form-label'>內容</label>
                                    <textarea class='form-control input-rounded' type='text' id='Content' name='Content' required></textarea>
                                </div>
                                <div class='col-12'>
                                    <label for='Link' class='form-label'>連結</label>
                                    <input class='form-control input-rounded' type='text' id='Link' name='Link' required value='/panel/'>
                                </div>
                                <button type='submit' class='btn btn-rounded btn-primary mt-4 pr-4 pl-4 form-submit'><i class='fa fa-rocket'></i>&nbsp;&nbsp;&nbsp;發送</button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!--列出通知-->
                <div class='col-12 mt-4'>
                    <div class='card'>
                        <div class='card-body'>
                            <h1 class='header-title'>列出通知</h1>
                            <select style='font-size: 14px;' class='input-rounded form-select' id='uuid'>
                                <option selected disabled>請選擇</option>
                                {$user}
                            </select><br><br>
                            <div class='single-table'>
                                <div class='table-responsive'>
                                    <table class='table table-hover progress-table text-center' id='list-notify'>
                                        <thead class='text-uppercase'>
                                            <tr>
                                                <th scope='col'>ID</th>
                                                <th scope='col'>圖標</th>
                                                <th scope='col'>內容</th>
                                                <th scope='col'>連結</th>
                                                <th scope='col'>建立時間</th>
                                                <th scope='col'>動作</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                 
                <script>
                loadModules(['myself/page/notify'])
                </script>";
    }

    /**
     * 處理post請求
     * @return array 狀態 or 資料
     */
    public function post(array $data): array {
        /* 通知發送 */
        if($_GET['type'] == 'sendNotify'){
            /* 消毒 */
            $data['Content'] = filter_var(trim($data['Content']), FILTER_SANITIZE_STRING);
            $data['Icon'] = filter_var(trim($data['Icon']), FILTER_SANITIZE_STRING);
            $data['Link'] = filter_var(trim($data['Link']), FILTER_SANITIZE_STRING);
            $data['User'] = filter_var(trim($data['User']), FILTER_SANITIZE_STRING);
            $data['status'] = filter_var(trim($data['status']), FILTER_SANITIZE_NUMBER_INT);

            /* 未定義/空值檢查 */
            if(empty($data['Content']) || empty($data['Icon']) || empty($data['Link']) || empty($data['User']) || !ctype_digit(strval($data['status']))){
                return array(
                    'code' => 400,
                    'Message' => '欄位不能留空!',
                );
            }

            /* 狀態翻譯 */
            switch($data['status']){
                case 1:
                    $data['status'] = \panel\apis\notify::$Status_Success;
                    break;
                case 2:
                    $data['status'] = \panel\apis\notify::$Status_Danger;
                    break;
                case 3:
                    $data['status'] = \panel\apis\notify::$Status_Warning;
                    break;
                case 4:
                    $data['status'] = \panel\apis\notify::$Status_Info;
                    break;
                default:
                    $data['status'] = \panel\apis\notify::$Status_Primary;
                    break;
            }

            /* 發送 */
            if($this->notify->Send_notify($data['User'], $data['Icon'], $data['status'], $data['Link'], $data['Content'])){
                return array(
                    'code' => 200,
                    'Message' => '通知發送成功!',
                );
            }else{
                return array(
                    'code' => 500,
                    'Message' => '通知發送失敗!',
                );
            }

        }
        /* 列出通知 */
        if($_GET['type'] == 'ShowNotify'){
            $data['uuid'] = filter_var(trim($data['uuid']), FILTER_SANITIZE_STRING);

            try {
                $notify = $this->notify->Show_notify($data['uuid']);
                if(!empty($notify)){
                    return array(
                        'code' => 200,
                        'data' => $notify
                    );
                }else{
                    return array(
                        'code' => 201,
                        'Message' => '沒有通知!',
                    );
                }
            } catch (\Exception $e) {
                return array(
                    'code' => 500,
                    'Message' => 'Database Error!',
                );
            }
        }
        /* 刪除通知 */
        if($_GET['type'] == 'DelNotify'){
            $data['id'] = filter_var(trim($data['id']), FILTER_SANITIZE_NUMBER_INT);

            /* 類型/空值檢查 */
            if(!ctype_digit(strval($data['id']))){
                return array(
                    'code' => 400,
                    'Message' => '欄位不能留空!'
                );
            }

            /* 删除 */
            if($this->notify->Delete_notify($data['id'])){
                return array(
                    'code' => 200,
                    'Message' => '通知成功删除!',
                );

            }else{
                return array(
                    'code' => 500,
                    'Message' => 'Database Error!'
                );
            }
        }

        return array(
            'code' => 500,
            'Message' => '請求出在錯! 檢查url!',
        );
    }

    /**
     * path輸出
     * @return string 輸出
     */
    public function path(): string {
        return "<li><a href='/panel/'>".showText("index.home")."</a></li>
                        <li><span>通知</span></li>";
    }
}