<?php
/*
 * Copyright (c) 2020.
 * Create by cocomine
 */

namespace panel\page;

use cocomine\IPage;
use mysqli;

/**
 * Class changesetting
 * @package cocopixelmc\Page
 */
class changesetting implements IPage {

    public static int $Role = 1;

    /**
     * changesetting constructor.
     * sql連接
     * @param mysqli $sqlcon sql連接
     * @param array $UpPath 上條路徑
     */
    function __construct(mysqli $sqlcon, array $UpPath) {}

    /* 檢查訪問權 */
    public function access(bool $isAuth, int $role): int {
        if(!$isAuth) return 401;
        if($role < self::$Role) return 403;
        return 200;
    }

    public function get_Title(): string {
        return showText("ChangeSetting.title");
    }

    public function get_Head(): string {
        return showText("ChangeSetting.setting");
    }

    /**
     * 輸出頁面
     * @return string 輸出頁面內容
     */
    function showPage(): string {
        global $auth;
        $userdata = $auth->userdata;
        $avatar = md5(strtolower($userdata['Email'])); //頭像

        // 指引文字
        $Text = showText('ChangeSetting');

        /* 用戶語言 */
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
        if ($userdata['ALLData']['2FA']) {
            $TwoFA = "<p>
                            <span style='color: limegreen'><i class='fa fa-lock pe-1'></i>{$Text['2FA']['is_Enable'][0]}</span><br>
                            {$Text['2FA']['is_Enable'][1]}<br>
                            <small class='text-muted'>" . showText("ChangeSetting.2FA.is_Enable.2") . "</small>
                      </p>
                      <button type='button' class='btn btn-rounded btn-primary mt-4 pr-4 pl-4 me-2' data-bs-toggle='modal' data-bs-target='#TwoFA_BackupCode'><i class='fa fa-eye pe-2'></i>{$Text['2FA']['show_BackupCode']}</button>
                      <button type='button' class='btn btn-rounded btn-secondary mt-4 pr-4 pl-4' data-bs-toggle='modal' data-bs-target='#TwoFA_confirm_off'><span class='ti-reload pe-2'></span>{$Text['2FA']['reset']}</button>";
        } else {
            $TwoFA = "<p>{$Text['2FA']['is_not_Enable']}</p>
                      <button type='button' class='btn btn-rounded btn-primary mt-4 pr-4 pl-4' data-bs-toggle='modal' data-bs-target='#TwoFA_register'><i class='fa fa-lock pe-2'></i>{$Text['2FA']['Enable']}</button>";
        }

        /* HTNL */
        return "<!-- 基本資料 -->
                <div class='col-12 mt-4'>
                    <div class='card'>
                        <div class='card-body'>
                            <h1 class='header-title'>{$Text['ChangeData']}</h1>
                            <div class='row justify-content-center'>
                                <!-- 大頭貼 -->
                                <div class='col-12 col-sm-8 col-md-6 col-lg-4 col-xl-3 position-relative'>
                                    <div class='ratio ratio-1x1 rounded-circle border-primary border-4 border overflow-hidden'>
                                        <img class='h-100 w-100' src='https://www.gravatar.com/avatar/{$avatar}?s=200' alt='avatar'>
                                        <div class='bg-black bg-opacity-75 position-absolute bottom-0 start-0 end-0 text-center pt-1' style='top: 75%'>
                                            <a class='link-light' href='https://gravatar.com' target='_blank'>
                                                <span class='ti-pencil pe-1'></span>{$Text['Gravatar']}
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <!-- 表單 -->
                                <div class='col-12 col-lg'>
                                    <form id='DataSet' novalidate class='needs-validation'>
                                        <div class='col-12'>
                                            <label for='Name' class='col-form-label'>{$Text['Name']['Name']}</label>
                                            <input class='form-control input-rounded' type='text' value='{$userdata["Name"]}' maxlength='16' id='Name' name='name' autocomplete='nickname' required>
                                            <small class='form-text text-muted'>{$Text['Name']['limit']}</small>
                                            <div class='invalid-feedback'></div>
                                        </div>
                                        <div class='col-12'>
                                            <label for='Email' class='col-form-label'>{$Text['Email']['Email']}</label>
                                            <input class='form-control input-rounded' type='email' value='{$userdata["Email"]}' id='Email' name='email' autocomplete='email' required inputmode='email' pattern='^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$'>
                                            <small class='form-text text-muted'>{$Text['Email']['addMSG']}</small>
                                            <div class='invalid-feedback'></div>
                                        </div>
                                        <div class='col-12'>
                                            <label class='col-form-label' for='Language'>{$Text['Lang']['Lang']}</label>
                                            <select class='input-rounded form-select' name='language' id='Language'>
                                                <option value='en' {$Lang_Sel[0]}>{$Text['Lang']['en']}</option>
                                                <option value='zh' {$Lang_Sel[1]}>{$Text['Lang']['zh']}</option>
                                                <option value='zh-CN' {$Lang_Sel[2]}>{$Text['Lang']['zh-CN']}</option>
                                            </select>
                                            <div class='invalid-feedback'></div>
                                        </div>
                                        <button type='submit' class='btn btn-rounded btn-primary mt-4 pr-4 pl-4 form-submit'><i class='fa fa-save pe-2'></i>{$Text['Submit']}</button>
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
                            <h1 class='header-title'>{$Text['Pass']['Pass']}</h1>
                            <form id='PassSet' novalidate class='needs-validation'>
                                <div class='col-12'>
                                    <label for='Old_Pass' class='col-form-label'>{$Text['Pass']['OldPass']}</label>
                                    <input class='form-control input-rounded' type='password' id='Old_Pass' pattern='(?=.*?[A-Z])(?=.*?[a-z]).{8,}' name='passwordOld' autocomplete='current-password' required>
                                    <div class='invalid-feedback'></div>
                                </div>
                                <div class='col-12'>
                                    <label for='New_Pass' class='col-form-label'>{$Text['Pass']['NewPass']}</label>
                                    <input class='form-control input-rounded' type='password' id='Password' pattern='(?=.*?[A-Z])(?=.*?[a-z]).{8,}' name='password' autocomplete='new-password' required>
                                    <div class='invalid-feedback'></div>
                                </div>
                                <div class='col-12'>
                                    <label for='CPass' class='col-form-label'>{$Text['Pass']['NewConfirmPass']}</label>
                                    <input class='form-control input-rounded' type='password' id='Password2' pattern='(?=.*?[A-Z])(?=.*?[a-z]).{8,}' name='password2' autocomplete='new-password' required data-placement='auto' data-toggle='popover' data-html='true' data-trigger='manual' data-content='<i class=\"ti-alert\" style=\"color:red;\"></i> " . showText("ChangeSetting.Pass.NotMach") . "'>
                                    <div class='invalid-feedback'></div>
                                </div>
                                <div class='col-12 col-md-6 mt-4'>
                                    <p>
                                        {$Text['Pass']['passStrength']}
                                        <div class='progress'>
                                            <div class='progress-bar' role='progressbar' style='width: 0%' id='passStrength'></div>
                                        </div>
                                    </p>
                                    <p>
                                        <b>{$Text['Pass']['condition'][0]}</b>
                                        <ol id='passStrength-list'>
                                            <li>{$Text['Pass']['condition'][1]}</li>
                                            <li>{$Text['Pass']['condition'][2]}</li>
                                            <li>{$Text['Pass']['condition'][3]}</li>
                                            <li>{$Text['Pass']['condition'][4]}</li>
                                        </ol>
                                    </p>
                                </div>
                                <button type='submit' class='btn btn-rounded btn-primary mt-4 pr-4 pl-4 form-submit'><i class='fa fa-save pe-2'></i>{$Text['Submit']}</button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- 雙重驗證 -->
                <div class='col-12 mt-4'>
                    <div class='card'>
                        <div class='card-body'>
                            <h1 class='header-title'>{$Text['2FA']['title']}</h1>
                            {$TwoFA}
                            <!-- 登記表單 -->
                            <div id='TwoFA_register' class='modal fade'>
                                <div class='modal-dialog modal-lg'>
                                    <div class='modal-content'>
                                        <div class='modal-header'>
                                            <h5 class='modal-title'><b>{$Text['2FA']['title']}</b></h5>
                                        </div>
                                        <div class='modal-body'>
                                            <p>{$Text['2FA']['2FA_register_modal']['body'][0]}</p>
                                            <div class='row justify-content-center'>
                                                <img src='' id='qr' alt='loading' class='visually-hidden'>
                                                <lottie-player src='https://assets1.lottiefiles.com/packages/lf20_a2chheio.json'  background='transparent'  speed='1'  style='width: 300px; height: 300px;'  loop  autoplay></lottie-player>
                                            </div>
                                            <p>{$Text['2FA']['2FA_register_modal']['body'][1]}</p>
                                            <pre id='secret' class='text-center text-uppercase fs-2 bg-secondary bg-opacity-50' style='color: #dc3545;'><div id='pre-submit-load' style='height: 40px; margin-top: -5px'> <div class='submit-load'><div></div><div></div><div></div><div></div></div> </div></pre>
                                        </div>
                                        
                                        <form id='TwoFASet' novalidate class='needs-validation'>
                                            <div class='modal-body' style='border-top: 1px solid #dee2e6;'>
                                                <div class='col-12'>
                                                    <label for='2FA_Code' class='col-form-label'>{$Text['2FA']['2FA_register_modal']['Enter_code']}</label>
                                                    <input class='form-control input-rounded' type='text' pattern='[0-9]{6}' id='2FA_Code' name='TwoFA_Code' autocomplete='off' required maxlength='6' inputmode='numeric'>
                                                 </div>
                                            </div>
                                            <div class='modal-footer'>
                                                <button type='button' class='btn btn-rounded btn-secondary' data-dismiss='modal'><i class='fa fa-arrow-left pe-2'></i>{$Text['2FA']['2FA_register_modal']['No']}</button>
                                                <button type='submit' class='btn btn-rounded btn-primary' disabled><i class='fa fa-check pe-2'></i>{$Text['2FA']['2FA_register_modal']['Enable']}</button>
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
                                            <h5 class='modal-title'><b>{$Text['2FA']['TwoFA_confirm_off_modal']['title']}</b></h5>
                                        </div>
                                        <div class='modal-body'>
                                            <p>{$Text['2FA']['TwoFA_confirm_off_modal']['body']}</p>
                                        </div>
                                        
                                        <div class='modal-footer'>
                                            <form id='2FAReset' novalidate class='needs-validation'>
                                                <button type='button' class='btn btn-rounded btn-secondary' data-dismiss='modal'><i class='fa fa-arrow-left pe-2'></i>{$Text['2FA']['TwoFA_confirm_off_modal']['NO']}</button>&nbsp;&nbsp;
                                                <button type='submit' class='btn btn-rounded btn-danger'><i class='fa fa-close pe-2'></i>{$Text['2FA']['TwoFA_confirm_off_modal']['YES']}</button>
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
                                            <h5 class='modal-title'><b>{$Text['2FA']['TwoFA_BackupCode_modal']['title']}</b></h5>
                                            <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                                        </div>
                                        <div class='modal-body'>
                                            <p>{$Text['2FA']['TwoFA_BackupCode_modal']['body']}</p>
                                            <div class='table-responsive mt-2' id='BackupCodeShowArea'></div>
                                            <div class='row justify-content-center' id='BackupCodeLoading'>
                                                <lottie-player src='https://assets1.lottiefiles.com/packages/lf20_a2chheio.json'  background='transparent'  speed='1'  style='width: 300px; height: 300px;'  loop  autoplay></lottie-player>
                                            </div>
                                        </div>
                                        <div class='modal-footer'>
                                            <button type='button' class='btn btn-rounded btn-primary' id='Download_BackupCode' disabled><i class='fa fa-download pe-2'></i>{$Text['2FA']['TwoFA_BackupCode_modal']['download']}</button>
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
                        zxcvbn: ['https://cdnjs.cloudflare.com/ajax/libs/zxcvbn/4.4.2/zxcvbn'],
                        forge: ['https://cdn.jsdelivr.net/npm/node-forge/dist/forge.min'],
                        FileSaver: ['FileSaver.min'],
                    },
                });
                loadModules(['myself/page/ChangeSetting', 'zxcvbn', 'forge', 'FileSaver'])
                </script>";
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
                    //openssl_public_encrypt($ALLCode[$i][$auth->sqlsetting_2FA_BackupCode['Code']], $ALLCode[$i][$auth->sqlsetting_2FA_BackupCode['Code']], $piKey);
                    //$ALLCode[$i][$auth->sqlsetting_2FA_BackupCode['Code']] = base64_encode($ALLCode[$i][$auth->sqlsetting_2FA_BackupCode['Code']]);
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