<?php
/*
 * Copyright (c) 2022.
 * Create by cocomine
 */

namespace panel\page\admin;

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
        if (!$isAuth) return 401;
        if ($role < 2) return 403;
        return 200;
    }

    /**
     * @inheritDoc
     */
    public function showPage(): string {

        /* 翻譯文件 */
        $Text = showText('Account.Content');
        $datatables_lang_url = showText('datatables_js.url');

        /* 身份組3以上可以增加user */
        $createAC_html = "";
        if ($this->role >= 3) $createAC_html = <<<tmp
<div class="col-12 mt-4">
    <div class="card">
        <div class="card-body">
            <h4 class="header-title">{$Text['Create']}</h4>
            <form id='add-ac' novalidate class='needs-validation'>
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
                            <option value='2'>{$Text['Role']['collaborator']}</option>
                            <option value='3'>{$Text['Role']['Administrator']}</option>
                        </select>
                    </div>
                    <div class='col-12 col-md-4'>
                        <label for='Pass' class='col-form-label'>{$Text['password']['password']}</label>
                        <input class='form-control input-rounded' type='text' id='Pass' disabled value="X-Trave!">
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
            switch ($row['role']) {
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
            if ($row['activated']) $active = '<span class="status-p bg-success">' . $Text['List']['Activated'] . '</span>';
            else $active = '<span class="status-p bg-danger">' . $Text['List']['Not_activated'] . '</span>';

            //time 處理;
            $time = date('Y-m-d G:i:s', $row['Last_Login']);

            $user_data .= "<tr class='position-relative'>
                <td>{$row['UUID']}</td>
                <td><a href='#' data-id='{$row['UUID']}' class='stretched-link'>{$row['Name']}</a></td>
                <td>{$row['Email']}</td
                ><td>$time</td>
                <td>$role</td
                ><td>$active</td>
            </tr>";
        }

        /* json */
        $LangJson = json_encode(array(
            'Activated' => $Text['List']['Activated'],
        ));

        return <<<body
$createAC_html
<pre id='LangJson' style='display: none'>$LangJson</pre>
<pre class="d-none" id="datatables_lang_url">$datatables_lang_url</pre>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.1/css/dataTables.bootstrap5.min.css"/>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.4.0/css/responsive.bootstrap5.min.css"/>
<div class="col-12">
    <div class="card">
        <div class="card-body">
            <h4 class="header-title">{$Text['List']['List']}</h4>
            <div class="alert alert-info"><i class="fa-solid fa-circle-info me-2"></i>選擇用戶查看資料</div>
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
<!-- 帳戶詳細資料 -->
<div class="modal" tabindex="-1" id="view-detail">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">帳戶詳細資料</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a class="nav-link active" id="user-detail-tab" data-bs-toggle="tab" data-bs-target="#user-detail-pane" type="button" role="tab" aria-controls="user-detail-pane" aria-selected="true">個人資料</a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="organization-detail-tab" data-bs-toggle="tab" data-bs-target="#organization-detail-pane" type="button" role="tab" aria-controls="organization-detail-pane" aria-selected="false">組織資料</a>
                    </li>
                </ul>
                <div class="tab-content mt-1">
                    <!-- 個人資料 -->
                    <div class="tab-pane fade show active" id="user-detail-pane" role="tabpanel" aria-labelledby="user-detail-tab" tabindex="0">
                        <div class="alert alert-warning" id="user-detail-none"><i class="fa-solid fa-triangle-exclamation me-2"></i>此帳戶沒有填寫個人資料</div>
                        <div class="row gy-2" id="user-detail">
                            <div class="col-6">
                                <label class="form-label" for="lastname">姓氏</label>
                                <input type="text" class="form-control form-rounded" id="lastname" readonly>
                            </div>
                            <div class="col-6">
                                <label class="form-label" for="firstname">名字</label>
                                <input type="text" class="form-control form-rounded" id="firstname" readonly>
                            </div>
                            <div class="col-6">
                                <label class="form-label" for="country">國家 / 地區</label>
                                <select class="form-control form-rounded crs-country" id="country" readonly data-value="shortcode" data-default-option="請選擇" data-region-id="null" disabled style="background-color: initial"></select>
                            </div>
                            <div class="col-6">
                                <label class="form-label" for="phone">電話號碼</label>
                                <input type="text" class="form-control form-rounded" id="phone" readonly>
                            </div>
                            <div class="col-6">
                                <label class="form-label" for="sex">性別</label>
                                <input type="text" class="form-control form-rounded" id="sex" readonly>
                            </div>
                            <div class="col-6">
                                <label class="form-label" for="birth">出生日期</label>
                                <input type="text" class="form-control form-rounded" id="birth" readonly>
                            </div>
                        </div>
                    </div>
                    <!-- 組織資料 -->
                    <div class="tab-pane fade" id="organization-detail-pane" role="tabpanel" aria-labelledby="organization-detail-tab" tabindex="0">
                        <div class="alert alert-warning" id="organize-detail-none"><i class="fa-solid fa-triangle-exclamation me-2"></i>此帳戶沒有填寫組織資料</div>
                        <div class="row gy-2" id="organize-detail">
                            <div class="row">
                                <div class='col-12'>
                                    <label for='organize-Name' class='col-form-label'>組織名字</label>
                                    <input class='form-control form-rounded' type='text' id='organize-Name' readonly>
                                </div>
                                <div class='col-5'>
                                    <label for="organize-bankCode" class='col-form-label'>銀行SWIFT代碼</label>
                                    <input class='form-control form-rounded' type='text' id='organize-bankCode'  readonly>
                                </div>
                                <div class='col-7'>
                                    <label for="organize-bankAccount" class='col-form-label'>銀行帳號號碼</label>
                                    <input class='form-control form-rounded' type='text' id='organize-bankAccount' readonly>
                                </div>
                                <div class='col-6'>
                                    <label class='col-form-label' for='organize-country'>組織所在國家 / 地區</label>
                                    <select class="form-control form-rounded crs-country" id="organize-country" readonly data-value="shortcode" data-default-option="請選擇" data-region-id="null" disabled style="background-color: initial"></select>
                                </div>
                                <div class='col-6'>
                                    <label for='organize-phone' class='col-form-label'>組織電話號碼</label>
                                    <input type="text" class="form-control form-rounded" id="organize-phone" readonly>
                                </div>
                                <div class='col-12'>
                                    <label for='organize-Address' class='col-form-label'>組織所在地址</label>
                                    <textarea class='form-control input-rounded' type='text' id='organize-Address' readonly style="resize: none"></textarea>
                                </div>
                                <div class="col-12">
                                    <label for='organize-prove' class='col-form-label'>商業證明</label><br>
                                    <a role="button" id="organize-prove" class='btn btn-rounded btn-primary pr-4 pl-4' target="_blank"><i class="fa-regular fa-eye me-2"></i>查看商業證明</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
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
    loadModules(['datatables.net', 'datatables.net-bs5', 'datatables.net-responsive', 'datatables.net-responsive-bs5', 'myself/page/admin/account', 'full.jquery.crs.min'])
</script>
body;
    }

    /**
     * @inheritDoc
     */
    function post(array $data): array {
        global $auth;

        /* 查看帳戶詳細資料 */
        if ($_GET['type'] === "viewDetail") {
            /* 個人資料 */
            $stmt = $this->sqlcon->prepare("SELECT d.*, u.role FROM User_detail d RIGHT JOIN User u ON d.UUID = u.UUID WHERE u.`UUID` = ?");
            $stmt->bind_param("s", $data['id']);
            if (!$stmt->execute()) {
                return array(
                    'code' => 500,
                    'Title' => showText('Error_Page.500_title'),
                    'Message' => $stmt->error,
                );
            }
            $row = $stmt->get_result()->fetch_assoc();
            $user_detail = $row['UUID'] ? $row : null;

            /* 組織資料 */
            if ($row['role'] >= 2) {
                $stmt->prepare("SELECT * FROM User_detail_collabora u WHERE UUID = ?");
                $stmt->bind_param("s", $data['id']);
                if (!$stmt->execute()) {
                    return array(
                        'code' => 500,
                        'Title' => showText('Error_Page.500_title'),
                        'Message' => $stmt->error,
                    );
                }
                $result = $stmt->get_result();
                $organize_detail = $result->num_rows >= 1 ? $result->fetch_assoc() : null;
            } else {
                $organize_detail = false; // 用戶是普通用戶
            }

            return array(
                'code' => 200,
                'data' => array(
                    'user_detail' => $user_detail,
                    'organize_detail' => $organize_detail,
                )
            );
        }

        /* 創建帳號 */
        $status = $auth->create_account($data['name'], $data['email'], 'X-Trave!', $data['role']);

        /* 設置強制更改 */
        if ($status == AUTH_REGISTER_COMPLETE) {
            $stmt = $this->sqlcon->prepare("SET @uuid = (SELECT UUID FROM User WHERE Email = ?)");
            $stmt->bind_param('s', $data['email']);
            if (!$stmt->execute()) $status = AUTH_SERVER_ERROR;
            //$stmt->prepare("INSERT INTO pwd_change (UUID) VALUES (@uuid)");
            //if(!$stmt->execute()) $status = AUTH_SERVER_ERROR;
            $stmt->prepare("SELECT @uuid AS `UUID`");
            if (!$stmt->execute()) $status = AUTH_SERVER_ERROR;

            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $uuid = $row['UUID'];
        }

        return array(
            'code' => $status,
            'Message' => $this->ResultMsg($status),
            'data' => array(
                'UUID' => $uuid ?? null
            )
        );
    }

    /**
     * @inheritDoc
     */
    function path(): string {
        return "<li><span><a href='/panel/'>" . showText("index.home") . "</a></span></li>
            <li><span><a href='/panel/admin/account/'>" . showText("admin.Head") . "</a></span></li>
            <li><span>" . showText("Account.Head") . "</span></li>";
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