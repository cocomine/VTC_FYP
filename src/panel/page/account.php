<?php
/*
 * Copyright (c) 2022.
 * Create by cocomine
 */

namespace panel\page;

use cocomine\IPage;
use mysqli;

class account implements IPage {

    private mysqli $sqlcon;
    private int $role;

    /**
     * @param mysqli $conn
     * @param array $upPath
     */
    public function __construct(mysqli $conn, array $upPath) {
        $this->sqlcon = $conn;
    }

    /**
     * @inheritDoc
     */
    public function access(bool $isAuth, int $role, bool $isPost): int {
        $this->role = $role;
        if ($isAuth && $role >= 2) return 200;
        return 403;
    }

    /**
     * @inheritDoc
     */
    public function showPage(): string {

        $Text = showText('Account.Content'); //翻譯文件

        /* 身份組3以上可以增加user */
        $createAC_html = "";
        if ($this->role >= 3) $createAC_html = <<<tmp
<div class="col-12 mt-4">
    <div class="card">
        <div class="card-body">
            <h4 class="header-title">Create Account</h4>
            <form id='AddAC' novalidate class='needs-validation'>
                <div class="row g-2">
                    <div class='col-12 col-md-4'>
                        <label for='Name' class='col-form-label'>{$Text['Name']['Name']}</label>
                        <input class='form-control input-rounded' type='text' maxlength='16' id='Name' name='name' required>
                        <small class='form-text text-muted'>{$Text['Name']['limit']}</small>
                        <div class='invalid-feedback'>{$Text['Form']['Cant_EMPTY']}</div>
                    </div>
                    <div class='col-12 col-md-4'>
                        <label for='Email' class='col-form-label'>{$Text['Email']}</label>
                        <input class='form-control input-rounded' type='email' id='Email' name='email' required inputmode='email' pattern='^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$'>
                        <div class='invalid-feedback'>{$Text['Form']['Error_format']}</div>
                    </div>
                    <div class='col-12 col-md-4'>
                        <label class='col-form-label' for='Role'>Role</label>
                        <select class='input-rounded form-select' name='role' id='Role'>
                            <option value='1'>{$Text['Role']['Normal']}</option>
                            <option value='2'>{$Text['Role']['Operator']}</option>
                            <option value='3'>{$Text['Role']['Administrator']}</option>
                        </select>
                    </div>
                    <div class='col-12 col-md-4'>
                        <label for='Pass' class='col-form-label'>{$Text['password']['password']}</label>
                        <input class='form-control input-rounded' type='text' id='Pass' disabled value="IVEairline!">
                        <small class='form-text text-muted'>{$Text['password']['limit']}</small>
                    </div>
                    <div class="w-100"></div>
                    <button type='submit' class='col-auto btn btn-rounded btn-primary mt-4 pr-4 pl-4 form-submit'><i class="fa-solid fa-plus me-2"></i>{$Text['Create']}</button>
                </div>
            </form>
        </div>
    </div>
</div>
tmp;

        /* 取得資料 */
        $stmt = $this->sqlcon->prepare("SELECT UUID, Name, Email, Last_Login, role, activated FROM User;");
        if (!$stmt->execute()) return "";

        /* 處理資料 */
        $result = $stmt->get_result();
        $user_data = "";
        while ($row = $result->fetch_assoc()) {
            // role 處理
            switch ($row['role']){
                case 1:
                    $role = $Text['Role']['Normal'];
                    break;
                case 2:
                    $role = $Text['Role']['Operator'];
                    break;
                case 3:
                    $role = $Text['Role']['Administrator'];
                    break;
                default:
                    $role = $row['role'];
                    break;
            }

            //activated 處理
            if($row['activated']) $active = '<span class="status-p bg-success">'.$Text['List']['Activated'].'</span>';
            else $active = '<span class="status-p bg-danger">'.$Text['List']['Not_activated'].'</span>';

            //time 處理;
            $time = date('Y-m-d G:i:s', $row['Last_Login']);

            $user_data .= "<tr><td>{$row['UUID']}</td><td>{$row['Name']}</td><td>{$row['Email']}</td><td>$time</td><td>$role</td><td>$active</td></tr>";
        }

        return <<<body
$createAC_html
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.1/css/dataTables.bootstrap5.min.css"/>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.4.0/css/responsive.bootstrap5.min.css"/>
<div class="col-12">
    <div class="card">
        <div class="card-body">
            <h4 class="header-title">{$Text['List']['List']}</h4>
            <div class="data-tables datatable-primary">
                <table id="dataTable" class="text-center w-100">
                    <thead class="text-capitalize">
                        <tr>
                            <th>{$Text['List']['ID']}</th>
                            <th>{$Text['List']['Name']}</th>
                            <th>{$Text['List']['Email']}</th>
                            <th>{$Text['List']['Time']}</th>
                            <th>{$Text['Role']['Role']}</th>
                            <th>{$Text['List']['Active']}</th>
                        </tr>
                    </thead>
                    <tbody>$user_data</tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script>
    require.config({
        paths:{
            'datatables.net': ['https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min'],
            'datatables.net-bs5': ['https://cdn.datatables.net/1.13.1/js/dataTables.bootstrap5.min'],
            'datatables.net-responsive': ['https://cdn.datatables.net/responsive/2.4.0/js/dataTables.responsive.min'],
            'datatables.net-responsive-bs5': ['https://cdn.datatables.net/responsive/2.4.0/js/responsive.bootstrap5'],
        },
    });
    loadModules(['datatables.net', 'datatables.net-bs5', 'datatables.net-responsive', 'datatables.net-responsive-bs5', 'myself/page/account'])
</script>
body;
    }

    /**
     * @inheritDoc
     */
    function post(array $data): array {
        global $auth;

        $status = $auth->create_account($data['name'], $data['email'], 'IVEairline!', $data['role']);

        /* 設置強制更改 */
        //todo:

        return array(
            'code' => $status,
            'Message' => $this->ResultMsg($status),
        );
    }

    /**
     * @inheritDoc
     */
    function path(): string {
        return "<li><span><a href='/panel/'>" . showText("index.home") . "</a></span></li><li><span>" . showText("Account.Head") . "</span></li>";
    }

    /**
     * @inheritDoc
     */
    public function get_Title(): string {
        return showText('Account.Title');
    }

    /**
     * @inheritDoc
     */
    public function get_Head(): string {
        return showText('Account.Head');
    }

    /**
     * 翻譯結果訊息
     * @param int $type 類型
     * @return string 訊息
     */
    private function ResultMsg(int $type): string {
        switch ($type) {
            case AUTH_REGISTER_EMAIL_FAIL:
                return showText("Account.Content.EMAIL_FAIL");
            case AUTH_REGISTER_PASS_NOT_STRONG:
                return showText("Account.Content.PASS_NOT_STRONG");
            case AUTH_REGISTER_EMAIL_WRONG_FORMAT:
                return showText("Account.Content.EMAIL_WRONG_FORMAT");
            case AUTH_REGISTER_EMPTY:
                return showText("Account.Content.EMPTY");
            case AUTH_REGISTER_NAME_TOO_LONG:
                return showText('Account.Content.NAME_TOO_LONG');
            case AUTH_SERVER_ERROR:
                return showText("Error");
            case AUTH_REGISTER_COMPLETE:
                return showText('Account.Content.COMPLETE');
            default:
                return '';
        }
    }
}