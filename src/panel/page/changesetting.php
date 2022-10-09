<?php
/*
 * Copyright (c) 2020.
 * Create by cocomine
 */

namespace panel\page;

use cocopixelmc\Auth\MyAuth;
use mysqli;

/**
 * Class changesetting
 * @package cocopixelmc\Page
 */
class changesetting {

    private mysqli $sqlcon;
    private array $UpPath;
    public static int $Role = 1;

    /**
     * changesetting constructor.
     * sql連接
     * @param mysqli $sqlcon sql連接
     * @param array $UpPath 上條路徑
     */
    function __construct(mysqli $sqlcon, array $UpPath) {
        $this->sqlcon = $sqlcon;
        $this->UpPath = $UpPath;
    }

    /**
     * 檢查訪問權
     * @param int $role 用戶權限
     * @return bool 是否允許
     */
    public function is_access(int $role): bool {
        if ($role < $this::$Role) {
            return false;
        }
        if (count($this->UpPath) > 0) {
            return false;
        }
        return true;
    }

    public function get_Title(): string {
        return showText("ChangeSetting.title");
    }

    public function get_Head(): string {
        return showText("ChangeSetting.setting");
    }

    /**
     * 輸出頁面
     * @param $userdata array UserData
     * @return string 輸出頁面內容
     */
    function showPage(): string {
        global $auth;
        $userdata = $auth->userdata;
        $avatar = md5(strtolower(trim($userdata['Email']))); //頭像

        /* 語言 */
        $Lang_Sel = array();
        switch ($userdata['Language']) {
            case 'zh':
                $Lang_Sel[1] = 'selected';
                break;
            case 'zh-CN':
                $Lang_Sel[2] = 'selected';
                break;
            default:
                $Lang_Sel[0] = 'selected';
                break;
        }

        /* 雙重驗證狀態 */
        if ($userdata['ALLData']['2FA'] == false) {
            $TwoFA = "<p>" . showText("ChangeSetting.2FA.is_not_Enable") . "</p>
                      <input style='display: none' type='text' name='DoAction' value='open'>
                      <button type='button' class='btn btn-rounded btn-primary mt-4 pr-4 pl-4' data-toggle='modal' data-target='#TwoFA_register'><i class='fa fa-check-square-o'></i>&nbsp;&nbsp;&nbsp;" . showText("ChangeSetting.2FA.Enable") . "</button>";
        } else {
            $TwoFA = "<p><span style='color: limegreen'><i class='fa fa-lock'></i> <b>" . showText("ChangeSetting.2FA.is_Enable.0") . "</b></span> " . showText("ChangeSetting.2FA.is_Enable.1") . "<br><small class='text-muted'>" . showText("ChangeSetting.2FA.is_Enable.2") . "</small></p>
                      <button type='button' class='btn btn-rounded btn-primary mt-4 pr-4 pl-4' data-toggle='modal' data-target='#TwoFA_BackupCode'><i class='fa fa-eye'></i>&nbsp;&nbsp;&nbsp;" . showText("ChangeSetting.2FA.show_BackupCode") . "</button>&nbsp;&nbsp;
                      <button type='button' class='btn btn-rounded btn-secondary mt-4 pr-4 pl-4' data-toggle='modal' data-target='#TwoFA_confirm_off'><span class='ti-reload'></span>&nbsp;&nbsp;&nbsp;" . showText("ChangeSetting.2FA.reset") . "</button>";
        }


        /* HTNL */
        $page = "<!-- 基本資料 -->
                <div class='col-12 mt-4'>
                    <div class='card'>
                        <div class='card-body'>
                            <h1 class='header-title'>" . showText("ChangeSetting.ChangeData") . "</h1>
                            <div class='row'>
                                <!-- 大頭貼 -->
                                <div class='col-md-4' style='min-width: 250px; text-align: center'>
                                    <div style='position: absolute; left: 50%; margin-left: -100px; background: rgba(0, 0, 0, 0.7); width: 200px; height: 200px; text-align: center; clip-path: inset(73% 0 0 0); border-radius: 50%; border: #ff7112 3px solid'>
                                        <br><br><br><br><br><br><br>
                                        <a style='color: #fff;' href='https://gravatar.com' target='_blank'>
                                            <span class='ti-pencil'></span>
                                            " . showText("ChangeSetting.Gravatar") . "
                                        </a>
                                        <br><br><br><br>
                                    </div>
                                    <img style='border-radius: 50%; width: 200px; height: 200px; border: #ff7112 3px solid' src='https://www.gravatar.com/avatar/{$avatar}?s=200' alt='avatar'>
                                </div>
                                <!-- 表單 -->
                                <div class='col-md'>
                                    <form id='DataSet' method='post' action='/panel/ChangeSetting'>
                                        <div class='form-group'>
                                            <label for='Name' class='col-form-label'>" . showText("ChangeSetting.Name.Name") . "</label>
                                            <input class='form-control input-rounded' type='text' value='{$userdata["Name"]}' maxlength='16' id='Name' name='Name' autocomplete='nickname' required>
                                            <small class='form-text text-muted'>" . showText("ChangeSetting.Name.limit") . "</small>
                                        </div>
                                        <div class='form-group'>
                                            <label for='Email' class='col-form-label'>" . showText("ChangeSetting.Email.Email") . "</label>
                                            <input class='form-control input-rounded' type='email' value='{$userdata["Email"]}' id='Email' name='Email' autocomplete='username' required inputmode='email'>
                                            <small class='form-text text-muted'>" . showText("ChangeSetting.Email.addMSG") . "</small>
                                        </div>
                                        <div class='form-group'>
                                            <label class='col-form-label' for='Language'>" . showText("ChangeSetting.Lang.Lang") . "</label>
                                            <select style='font-size: 14px;' class='input-rounded custom-select' name='Language' id='Language'>
                                                <option value='en' " . @$Lang_Sel[0] . ">" . showText("ChangeSetting.Lang.en") . "</option>
                                                <option value='zh' " . @$Lang_Sel[1] . ">" . showText("ChangeSetting.Lang.zh") . "</option>
                                                <option value='zh-CN' " . @$Lang_Sel[2] . ">" . showText("ChangeSetting.Lang.zh-CN") . "</option>
                                            </select>
                                        </div>
                                        <button type='submit' class='btn btn-rounded btn-primary mt-4 pr-4 pl-4 form-submit'><i class='fa fa-save'></i>&nbsp;&nbsp;&nbsp;" . showText("ChangeSetting.Submit") . "</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- 密碼 -->
                <div class='col-12 mt-4'>
                    <div class='card'>
                        <div class='card-body'>
                            <h1 class='header-title'>" . showText("ChangeSetting.Pass.Pass") . "</h1>
                            <form id='PassSet' method='post' action='/panel/ChangeSetting'>
                                <div class='form-group'>
                                    <label for='Old_Pass' class='col-form-label'>" . showText("ChangeSetting.Pass.OldPass") . "</label>
                                    <input class='form-control input-rounded' type='password' id='Old_Pass' pattern='(?=.*?[A-Z])(?=.*?[a-z]).{8,}' name='Old_Pass' autocomplete='current-password' required>
                                </div>
                                <div class='form-group'>
                                    <label for='New_Pass' class='col-form-label'>" . showText("ChangeSetting.Pass.NewPass") . "</label>
                                    <input class='form-control input-rounded' type='password' id='Password' pattern='(?=.*?[A-Z])(?=.*?[a-z]).{8,}' name='New_Pass' autocomplete='new-password' required>
                                </div>
                                <div class='form-group'>
                                    <label for='CPass' class='col-form-label'>" . showText("ChangeSetting.Pass.NewConfirmPass") . "</label>
                                    <input class='form-control input-rounded' type='password' id='Password2' pattern='(?=.*?[A-Z])(?=.*?[a-z]).{8,}' name='Confirm_Pass' autocomplete='new-password' required data-placement='auto' data-toggle='popover' data-html='true' data-trigger='manual' data-content='<i class=\"ti-alert\" style=\"color:red;\"></i> " . showText("ChangeSetting.Pass.NotMach") . "'>
                                </div>
                                <div class=\"form-gp\">
                                    <p>
                                        " . showText("ChangeSetting.Pass.passStrength") . " <span class='input-rounded' id='password-strength-text'>--</span> <br> 
                                        <meter max='5' value='1' id='password-strength-meter'></meter>
                                    </p>
                                    <p>
                                        <b>" . showText("ChangeSetting.Pass.condition.0") . "</b><br>
                                        1. " . showText("ChangeSetting.Pass.condition.1") . "<br>
                                        2. " . showText("ChangeSetting.Pass.condition.2") . "<br>
                                        3. " . showText("ChangeSetting.Pass.condition.3") . "<br>
                                        4. " . showText("ChangeSetting.Pass.condition.4") . "
                                    </p>
                                </div>
                                <div class='form-group'>
                                    <input style='opacity: 0' type='email' autocomplete='username' name='Email'>
                                </div>
                                <button type='submit' class='btn btn-rounded btn-primary mt-4 pr-4 pl-4 form-submit'><i class='fa fa-save'></i>&nbsp;&nbsp;&nbsp;" . showText("ChangeSetting.Submit") . "</button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- 雙重驗證 -->
                <div class='col-12 mt-4'>
                    <div class='card'>
                        <div class='card-body'>
                            <h1 class='header-title'>" . showText("ChangeSetting.2FA.title") . "</h1>
                            {$TwoFA}
                            <!-- 登記表單 -->
                            <div id='TwoFA_register' class='modal fade'>
                                <div class='modal-dialog modal-lg'>
                                    <div class='modal-content'>
                                        <div class='modal-header'>
                                            <h5 class='modal-title'><b>" . showText("ChangeSetting.2FA.title") . "</b></h5>
                                        </div>
                                        <div class='modal-body'>
                                            <p>" . showText("ChangeSetting.2FA.2FA_register_modal.body.0") . "</p>
                                            <img src='/panel/assets/images/icon/loader.gif' id='qr'>
                                            <p>" . showText("ChangeSetting.2FA.2FA_register_modal.body.1") . "</p>
                                            <pre id='secret' style='color: #dc3545; font-size: 20px; background-color: rgba(161,161,161,0.78); text-align: center'><div id='pre-submit-load' style='height: 40px; margin-top: -5px'> <div class='submit-load'><div></div><div></div><div></div><div></div></div> </div></pre>
                                        </div>
                                        
                                        <form id='TwoFACheck' method='post' action='/panel/ChangeSetting'>
                                        <div class='modal-body' style='border-top: 1px solid #dee2e6;'>
                                            <div class='form-group'>
                                                <label for='2FA_Code' class='col-form-label'>" . showText("ChangeSetting.2FA.2FA_register_modal.Enter_code") . "</label>
                                                <input class='form-control input-rounded' type='text' pattern='[0-9]{6}' id='2FA_Code' name='TwoFA_Code' autocomplete='off' required maxlength='6' inputmode='numeric'>
                                                
                                             </div>
                                        </div>
                                        <div class='modal-footer'>
                                            <button type='button' class='btn btn-rounded btn-secondary' data-dismiss='modal'><i class='fa fa-arrow-left'></i>&nbsp;&nbsp;&nbsp;" . showText("ChangeSetting.2FA.2FA_register_modal.No") . "</button>
                                            <button type='submit' class='btn btn-rounded btn-primary' disabled><i class='fa fa-check-square-o'></i>&nbsp;&nbsp;&nbsp;" . showText("ChangeSetting.2FA.2FA_register_modal.Enable") . "</button>
                                        </div>
                                        </form>
                                        
                                    </div>
                                </div>
                            </div>
                            
                            <!-- 確認關閉 -->
                            <div id='TwoFA_confirm_off' class='modal fade'>
                                <div class='modal-dialog modal-dialog-centered'>
                                    <div class='modal-content'>
                                        <div class='modal-header'>
                                            <h5 class='modal-title'><b>" . showText("ChangeSetting.2FA.TwoFA_confirm_off_modal.title") . "</b></h5>
                                        </div>
                                        <div class='modal-body'>
                                            <p>" . showText("ChangeSetting.2FA.TwoFA_confirm_off_modal.body") . "</p>
                                        </div>
                                        
                                        <div class='modal-footer'>
                                            <form id='2FASet' method='post' action='/panel/ChangeSetting'>
                                                <input style='display: none' type='text' name='DoAction' value='reset'>
                                                <button type='button' class='btn btn-rounded btn-secondary' data-dismiss='modal'><i class='fa fa-arrow-left'></i>&nbsp;&nbsp;&nbsp;" . showText("ChangeSetting.2FA.TwoFA_confirm_off_modal.NO") . "</button>&nbsp;&nbsp;
                                                <button type='submit' class='btn btn-rounded btn-danger'><i class='fa fa-close'></i>&nbsp;&nbsp;&nbsp;" . showText("ChangeSetting.2FA.TwoFA_confirm_off_modal.YES") . "</button>
                                            </form>
                                        </div>
                                        
                                    </div>
                                </div>
                            </div>
                            
                            <!-- 安全代碼 -->
                            <div id='TwoFA_BackupCode' class='modal fade'>
                                <div class='modal-dialog'>
                                    <div class='modal-content'>
                                        <div class='modal-header'>
                                            <h5 class='modal-title'><b>" . showText("ChangeSetting.2FA.TwoFA_BackupCode_modal.title") . "</b></h5>
                                            <button type='button' class='close' data-dismiss='modal'><span>×</span></button>
                                        </div>
                                        <div class='modal-body'>
                                            <p>" . showText("ChangeSetting.2FA.TwoFA_BackupCode_modal.body") . "</p>
                                            <br>
                                            <div class='single-table'>
                                                <div class='table-responsive' id='BackupCodeShowArea'>
                                                    <div id='pre-submit-load' style='height: 20px'> <div class='submit-load'><div></div><div></div><div></div><div></div></div> </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class='modal-footer'>
                                            <button type='button' class='btn btn-rounded btn-primary' id='Download_BackupCode' disabled><i class='fa fa-download'></i>&nbsp;&nbsp;&nbsp;" . showText("ChangeSetting.2FA.TwoFA_BackupCode_modal.download") . "</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <link rel='stylesheet' href='/panel/assets/css/myself/meter.min.css'>
                <script>
                require.config({
                    paths:{
                        JSEncrypt: ['jsencrypt.min'],
                        zxcvbn: ['https://cdnjs.cloudflare.com/ajax/libs/zxcvbn/4.4.2/zxcvbn', 'https://cdn.jsdelivr.net/npm/zxcvbn@4.4.2/dist/zxcvbn'],
                        forge: ['https://cdn.jsdelivr.net/npm/node-forge@0.9.1/dist/forge.min', 'https://cdnjs.cloudflare.com/ajax/libs/forge/0.9.1/forge.min'],
                        password_strength_meter: ['myself/password-strength-meter.min'],
                        ChangeSetting: ['myself/page/ChangeSetting.min'],
                        FileSaver: ['FileSaver.min']
                    },
                });
                require(['JSEncrypt','zxcvbn', 'forge', 'FileSaver', 'password_strength_meter'], function(jsencrypt, zxcvbn) {
                  loadMeterText();
                  window.zxcvbn = zxcvbn;
                });
                require(['ChangeSetting'], function (ChangeSetting) {
                    window.ChangeSetting = ChangeSetting;
                });
                </script>";
        return $page;
    }

    /**
     * 回傳表單資料
     * @return array 彈出窗口
     */
    function post(): array {
        global $auth;

        $data = json_decode(file_get_contents("php://input"));

        /* 修改資料 */
        if ($_GET['type'] == 'DataSet') {
            $data = array(
                'Name' => $data->Name,
                'Email' => $data->Email,
                'Lang' => $data->Language
            );
            $auth->add_Hook('acc_changeSetting_data', 'acc_Activated_Mail_Hook');
            $status = $auth->changeSetting(AUTH_CHANGESETTING_DATA, $data);
            if ($status[0] === AUTH_CHANGESETTING_DATA_OK_EMAIL) {
                $OutPut = array(
                    'status' => 'Success',
                    'title' => showText("ChangeSetting.AUTH_CHANGESETTING_DATA_OK_EMAIL.0"),
                    'content' => showText("ChangeSetting.AUTH_CHANGESETTING_DATA_OK_EMAIL.1"),
                    'script' => "$('.user-name').html({$status[1]} + '<i class=\"fa fa-angle-down\"></i>');"
                );
            }
            if ($status[0] === AUTH_CHANGESETTING_DATA_OK) {
                $OutPut = array(
                    'status' => 'Success',
                    'title' => showText("ChangeSetting.AUTH_CHANGESETTING_DATA_OK"),
                    'script' => "$('.user-name').html('{$status[1]}' + '<i class=\"fa fa-angle-down\"></i>');"
                );
            }
            if ($status[0] === AUTH_CHANGESETTING_DATA_FAIL) {
                $OutPut = array(
                    'status' => 'Error',
                    'title' => showText("ChangeSetting.AUTH_CHANGESETTING_DATA_FAIL.0"),
                    'content' => showText("ChangeSetting.AUTH_CHANGESETTING_DATA_FAIL.1")
                );
            }
            if ($status[0] === AUTH_CHANGESETTING_DATA_FAIL_NOT_MATCH) {
                $OutPut = array(
                    'status' => 'Error',
                    'title' => showText("ChangeSetting.AUTH_CHANGESETTING_DATA_FAIL_NOT_MATCH.0"),
                    'content' => showText("ChangeSetting.AUTH_CHANGESETTING_DATA_FAIL_NOT_MATCH.1")
                );
            }
            return $OutPut;
        }
        if ($_GET['type'] == 'PassSet') {
            $data = array(
                'NewPass' => $data->New_Pass,
                'ConfirmPass' => $data->Confirm_Pass,
                'OldPass' => $data->Old_Pass
            );
            $status = $auth->changeSetting(AUTH_CHANGESETTING_PASS, $data);
            if ($status[0] === AUTH_CHANGESETTING_PASS_OK) {
                $OutPut = array(
                    'status' => 'Success',
                    'title' => showText("ChangeSetting.AUTH_CHANGESETTING_PASS_OK.0"),
                    'content' => showText("ChangeSetting.AUTH_CHANGESETTING_PASS_OK.1")
                );
            }
            if ($status[0] === AUTH_CHANGESETTING_PASS_FAIL) {
                $OutPut = array(
                    'status' => 'Error',
                    'title' => showText("ChangeSetting.AUTH_CHANGESETTING_PASS_FAIL.0"),
                    'content' => showText("ChangeSetting.AUTH_CHANGESETTING_PASS_FAIL.1")
                );
            }
            if ($status[0] === AUTH_CHANGESETTING_PASS_FAIL_NOT_STRONG) {
                $OutPut = array(
                    'status' => 'Error',
                    'title' => showText("ChangeSetting.AUTH_CHANGESETTING_PASS_FAIL_NOT_STRONG.0"),
                    'content' => showText("ChangeSetting.AUTH_CHANGESETTING_PASS_FAIL_NOT_STRONG.1")
                );
            }
            if ($status[0] === AUTH_CHANGESETTING_PASS_FAIL_NOT_MATCH) {
                $OutPut = array(
                    'status' => 'Error',
                    'title' => showText("ChangeSetting.AUTH_CHANGESETTING_PASS_FAIL_NOT_MATCH.0"),
                    'content' => showText("ChangeSetting.AUTH_CHANGESETTING_PASS_FAIL_NOT_MATCH.1")
                );
            }
            if ($status[0] === AUTH_CHANGESETTING_PASS_FAIL_EMPTY) {
                $OutPut = array(
                    'status' => 'Error',
                    'title' => showText("ChangeSetting.AUTH_CHANGESETTING_PASS_FAIL_EMPTY.0"),
                    'content' => showText("ChangeSetting.AUTH_CHANGESETTING_PASS_FAIL_EMPTY.1")
                );
            }
            if ($status[0] === AUTH_CHANGESETTING_PASS_FAIL_OLD_PASS_WRONG) {
                $OutPut = array(
                    'status' => 'Error',
                    'title' => showText("ChangeSetting.AUTH_CHANGESETTING_PASS_FAIL_OLD_PASS_WRONG.0"),
                    'content' => showText("ChangeSetting.AUTH_CHANGESETTING_PASS_FAIL_OLD_PASS_WRONG.1")
                );
            }
            return $OutPut;
        }
        if ($_GET['type'] == '2FASet') {
            $puKey = filter_var($data->puKey, FILTER_SANITIZE_STRING);;
            if ($data->DoAction == 'open') {
                $data = array(
                    '2FAto' => true,
                );
            } else {
                $data = array(
                    '2FAto' => false,
                );
            }

            $status = $auth->changeSetting(AUTH_CHANGESETTING_2FA, $data);
            if ($status[0] == AUTH_CHANGESETTING_2FA_LOGON) {
                $secret = wordwrap($status[1], 4, ' ', true); //分割

                /* 加密 */
                $piKey = openssl_pkey_get_public($puKey);
                $qr = base64_encode($status[2]);
                openssl_public_encrypt($secret, $secret, $piKey);
                $code = base64_encode($secret);

                $Output = array(
                    'QRcode' => $qr,
                    'code' => $code
                );
            }
            if ($status[0] == AUTH_CHANGESETTING_2FA_OFF_OK) {
                $Output = array(
                    'script' => "$('#TwoFA_confirm_off').modal('hide');",
                    'status' => 'Success',
                    'title' => showText("ChangeSetting.AUTH_CHANGESETTING_2FA_OFF_OK")
                );
            }
            if ($status[0] == AUTH_CHANGESETTING_2FA_OFF_FAIL) {
                $Output = array(
                    'status' => 'Error',
                    'title' => showText("ChangeSetting.AUTH_CHANGESETTING_2FA_OFF_FAIL.0"),
                    'content' => showText("ChangeSetting.AUTH_CHANGESETTING_2FA_OFF_FAIL.1"),
                );
            }

            return $Output;
        }
        if ($_GET['type'] == 'TwoFACheck') {
            $data = array(
                'code' => $data->TwoFA_Code
            );

            $status = $auth->changeSetting(AUTH_CHANGESETTING_2FA_CHECK_CODE, $data);
            if ($status[0] == AUTH_CHANGESETTING_2FA_CHECK_CODE_FAIL) {
                $outPut = array(
                    'status' => 'Error',
                    'title' => showText("ChangeSetting.AUTH_CHANGESETTING_2FA_CHECK_CODE_FAIL.0"),
                    'content' => showText("ChangeSetting.AUTH_CHANGESETTING_2FA_CHECK_CODE_FAIL.1"),
                    'modal' => true,
                    'script' => "ChangeSetting.bt_reset()"
                );
            }
            if ($status[0] == AUTH_CHANGESETTING_2FA_CHECK_CODE_OK) {
                $outPut = array(
                    'script' => "$('#TwoFA_register').modal('hide');",
                    'status' => 'Success',
                    'title' => showText("ChangeSetting.AUTH_CHANGESETTING_2FA_CHECK_CODE_OK.0"),
                    'content' => showText("ChangeSetting.AUTH_CHANGESETTING_2FA_CHECK_CODE_OK.1"),
                );
            }
            return $outPut;
        }
        if ($_GET['type'] == '2FABackupCode') {
            $status = $auth->changeSetting(AUTH_CHANGESETTING_2FA_SHOWBACKUPCODE, null);
            if ($status[0] == AUTH_CHANGESETTING_2FA_SHOWBACKUPCODE_OK) {
                /* 加密訊息 */
                $ALLCode = $status[1];
                $piKey = openssl_pkey_get_public(filter_var($_POST['puKey'], FILTER_SANITIZE_STRING));
                for ($i = 0; $i < sizeof($ALLCode); $i++) {
                    openssl_public_encrypt($ALLCode[$i][$auth->sqlsetting_2FA_BackupCode['Code']], $ALLCode[$i][$auth->sqlsetting_2FA_BackupCode['Code']], $piKey);
                    $ALLCode[$i][$auth->sqlsetting_2FA_BackupCode['Code']] = base64_encode($ALLCode[$i][$auth->sqlsetting_2FA_BackupCode['Code']]);
                }
                $outPut = array(
                    'UserName' => $auth->userdata['Name'],
                    'Code' => $ALLCode
                );
            }
            if ($status[0] == AUTH_CHANGESETTING_2FA_SHOWBACKUPCODE_FAIL) {
                $outPut = array(
                    'script' => "$('#TwoFA_BackupCode').modal('hide');",
                    'status' => 'Error',
                    'title' => showText("ChangeSetting.AUTH_CHANGESETTING_2FA_SHOWBACKUPCODE_FAIL.0"),
                    'content' => showText("ChangeSetting.AUTH_CHANGESETTING_2FA_SHOWBACKUPCODE_FAIL.1"),
                );
            }
            return $outPut;
        }

        return array(
            'status' => 'Error',
            'title' => '請求出在錯! 檢查url!',
        );
    }

    /**
     * path輸出
     * @return string 輸出
     */
    function path(): string {
        return "<li><a href='/panel' data-ajax='GET'>" . showText("index.Console") . "</a></li>
                <li><span>" . showText("ChangeSetting.setting") . "</span></li>";
    }
}