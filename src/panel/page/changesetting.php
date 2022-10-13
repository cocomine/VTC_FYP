<?php
/*
 * Copyright (c) 2020.
 * Create by cocomine
 */

namespace panel\page;

use cocomine\IPage;
use cocomine\MyAuthException;
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
    function __construct(mysqli $sqlcon, array $UpPath) { }

    /* 檢查訪問權 */
    public function access(bool $isAuth, int $role): int {
        if (!$isAuth) return 401;
        if ($role < self::$Role) return 403;
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

        /* json 語言 */
        $jsonLang = json_encode(array('strength' => showText('ChangeSetting.strength')));

        /* HTNL */
        return "<pre id='langJson' style='display: none'>$jsonLang</pre>
                <!-- 基本資料 -->
                <div class='col-12 mt-4'>
                    <div class='card'>
                        <div class='card-body'>
                            <h1 class='header-title'>{$Text['ChangeData']}</h1>
                            <div class='row justify-content-center'>
                                <!-- 大頭貼 -->
                                <div class='col-12 col-sm-8 col-md-6 col-lg-4 col-xl-3 position-relative'>
                                    <div class='ratio ratio-1x1 rounded-circle border-primary border-4 border overflow-hidden'>
                                        <img class='h-100 w-100' src='https://www.gravatar.com/avatar/$avatar?s=200' alt='avatar'>
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
                                            <div class='invalid-feedback'>{$Text['Form']['Cant_EMPTY']}</div>
                                        </div>
                                        <div class='col-12'>
                                            <label for='Email' class='col-form-label'>{$Text['Email']['Email']}</label>
                                            <input class='form-control input-rounded' type='email' value='{$userdata["Email"]}' id='Email' name='email' autocomplete='email' required inputmode='email' pattern='^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$'>
                                            <small class='form-text text-muted'>{$Text['Email']['addMSG']}</small>
                                            <div class='invalid-feedback'>{$Text['Form']['Error_format']}</div>
                                        </div>
                                        <div class='col-12'>
                                            <label class='col-form-label' for='Language'>{$Text['Lang']['Lang']}</label>
                                            <select class='input-rounded form-select' name='lang' id='Language'>
                                                <option value='en' $Lang_Sel[0]>{$Text['Lang']['en']}</option>
                                                <option value='zh' $Lang_Sel[1]>{$Text['Lang']['zh']}</option>
                                                <option value='zh-CN' $Lang_Sel[2]>{$Text['Lang']['zh-CN']}</option>
                                            </select>
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
                                    <div class='invalid-feedback'>{$Text['Form']['Cant_EMPTY']}</div>
                                </div>
                                <div class='col-12'>
                                    <label for='New_Pass' class='col-form-label'>{$Text['Pass']['NewPass']}</label>
                                    <input class='input-rounded form-control' type='password' id='Password' pattern='(?=.*?[A-Z])(?=.*?[a-z]).{8,}' name='password' autocomplete='new-password' required>
                                    <div class='invalid-feedback'>{$Text['Form']['Match_Old_Pass']}</div>
                                </div>
                                <div class='col-12'>
                                    <label for='CPass' class='col-form-label'>{$Text['Pass']['NewConfirmPass']}</label>
                                    <input class='form-control input-rounded' type='password' id='Password2' pattern='(?=.*?[A-Z])(?=.*?[a-z]).{8,}' name='password2' autocomplete='new-password' required data-placement='auto' data-toggle='popover' data-html='true' data-trigger='manual' data-content='<i class=\"ti-alert\" style=\"color:red;\"></i> " . showText("ChangeSetting.Pass.NotMach") . "'>
                                    <div class='invalid-feedback'>{$Text['Form']['Not_Match_Wrong']}</div>
                                </div>
                                <div class='col-12 col-md-6 mt-4'>
                                    <p>
                                        {$Text['Pass']['passStrength']}
                                        <div class='progress'>
                                            <div class='progress-bar' role='progressbar' style='width: 0' id='passStrength'></div>
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
                            $TwoFA
                            <!-- 登記表單 -->
                            <div id='TwoFA_register' class='modal fade' data-bs-backdrop='static' data-bs-keyboard='false' tabindex='-1'>
                                <div class='modal-dialog modal-lg'>
                                    <div class='modal-content'>
                                        <div class='modal-header'>
                                            <h5 class='modal-title'><b>{$Text['2FA']['title']}</b></h5>
                                        </div>
                                        <div class='modal-body'>
                                            <p>{$Text['2FA']['2FA_register_modal']['body'][0]}</p>
                                            <div class='row justify-content-center g-0' id='qr'>
                                                <lottie-player src='https://assets7.lottiefiles.com/packages/lf20_j3ndxy3v.json' background='transparent' speed='1' style='width: 300px; height: 300px;' loop autoplay></lottie-player>
                                            </div>
                                            <p>{$Text['2FA']['2FA_register_modal']['body'][1]}</p>
                                            <pre id='secret' class='text-center text-uppercase fs-2 bg-secondary bg-opacity-50' style='color: #dc3545;'><div id='pre-submit-load' style='height: 40px; margin-top: -5px'> <div class='submit-load'><div></div><div></div><div></div><div></div></div> </div></pre>
                                        </div>
                                        
                                        <form id='TwoFASet' novalidate class='needs-validation'>
                                            <div class='modal-body' style='border-top: 1px solid #dee2e6;'>
                                                <div class='col-12'>
                                                    <label for='2FA_Code' class='col-form-label'>{$Text['2FA']['2FA_register_modal']['Enter_code']}</label>
                                                    <input class='form-control input-rounded' type='text' pattern='[0-9]{6}' id='2FA_Code' name='TwoFA_Code' autocomplete='off' required maxlength='6' inputmode='numeric'>
                                                    <div class='invalid-feedback'>{$Text['Form']['Only_number']}</div>
                                                </div>
                                            </div>
                                            <div class='modal-footer'>
                                                <button type='button' class='btn btn-rounded btn-secondary' data-bs-dismiss='modal'><i class='fa fa-arrow-left pe-2'></i>{$Text['2FA']['2FA_register_modal']['No']}</button>
                                                <button type='submit' class='btn btn-rounded btn-primary form-submit' disabled><i class='fa fa-check pe-2'></i>{$Text['2FA']['2FA_register_modal']['Enable']}</button>
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
                                            <button type='button' class='btn btn-rounded btn-secondary' data-bs-dismiss='modal'><i class='fa fa-arrow-left pe-2'></i>{$Text['2FA']['TwoFA_confirm_off_modal']['NO']}</button>&nbsp;&nbsp;
                                            <button type='submit' class='btn btn-rounded btn-danger' id='2FAReset'><i class='fa fa-close pe-2'></i>{$Text['2FA']['TwoFA_confirm_off_modal']['YES']}</button>
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
                                                <lottie-player src='https://assets7.lottiefiles.com/packages/lf20_j3ndxy3v.json' background='transparent' speed='1' style='width: 300px; height: 300px;' loop autoplay></lottie-player>
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

    /* 回傳表單資料 */
    function post(array $data): array {
        global $auth;

        /* 修改資料 */
        if ($_GET['type'] == 'DataSet') {
            $auth->add_Hook('acc_changeSetting_data', 'acc_Activated_Mail_Hook');
            $status = $auth->changeDataSetting($data['email'], $data['name'], $data['lang']);
            return array(
                'code' => $status,
                'Title' => $this->ResultMsg($status)[0],
                'Message' => $this->ResultMsg($status)[1],
                'Data' => array(
                    'name' => $auth->userdata['Name'],
                    'email' => $auth->userdata['Email'],
                    'lang' => $auth->userdata['Language']
                )
            );
        }

        /* 修改密碼 */
        if ($_GET['type'] == 'PassSet') {
            $status = $auth->changePasswordSetting($data['passwordOld'], $data['password'], $data['password2'], $_SESSION['pvKey']);
            return array(
                'code' => $status,
                'Title' => $this->ResultMsg($status)[0],
                'Message' => $this->ResultMsg($status)[1],
            );
        }

        /* 開啟關閉2FA */
        if ($_GET['type'] == '2FASet') {
            $puKey = filter_var($data['puKey'], FILTER_SANITIZE_STRING);;
            $status = $auth->change2FASetting($data['DoAction']);
            if ($status == AUTH_CHANGESETTING_2FA_LOGON) {
                $twoFA = $auth->getTwoFA();

                /* 加密 */
                $piKey = openssl_pkey_get_public($puKey);
                $qr = base64_encode($twoFA->getQRCode($auth->userdata['Name']));
                openssl_public_encrypt($twoFA->getSecret(), $secret, $piKey);
                $code = base64_encode($secret);

                return array(
                    'code' => $status,
                    'Title' => $this->ResultMsg($status)[0],
                    'Message' => $this->ResultMsg($status)[1],
                    'Data' => array(
                        'secret' => $code,
                        'qr' => $qr
                    )
                );
            } else {
                return array(
                    'code' => $status,
                    'Title' => $this->ResultMsg($status)[0],
                    'Message' => $this->ResultMsg($status)[1],
                );
            }
        }

        /* 檢查2FA代碼 */
        if ($_GET['type'] == 'TwoFACheck') {
            $status = $auth->change2FASettingCheckCode($data['TwoFA_Code']);
            return array(
                'code' => $status,
                'Title' => $this->ResultMsg($status)[0],
                'Message' => $this->ResultMsg($status)[1],
            );
        }

        /* 展示備用代碼 */
        if ($_GET['type'] == '2FABackupCode') {
            $puKey = filter_var($data['puKey'], FILTER_SANITIZE_STRING);;
            try {
                $codes = $auth->change2FASettingShowBackupCode();

                /* 加密 */
                $piKey = openssl_pkey_get_public($puKey);
                $temp = array_map(function($item) use($piKey) {
                    openssl_public_encrypt($item['Code'], $item['Code'], $piKey);
                    $item['Code'] = base64_encode($item['Code']);
                    return $item;
                }, $codes);

                return array(
                    'code' => AUTH_CHANGESETTING_2FA_SHOWBACKUPCODE_OK,
                    'Title' => $this->ResultMsg(AUTH_CHANGESETTING_2FA_SHOWBACKUPCODE_OK)[0],
                    'Message' => $this->ResultMsg(AUTH_CHANGESETTING_2FA_SHOWBACKUPCODE_OK)[1],
                    'Data' => array(
                        'code' => $temp,
                        'username' => $auth->userdata['Name']
                    )
                );
            } catch (MyAuthException $e) {
                return array(
                    'code' => $e->getCode(),
                    'Title' => $this->ResultMsg($e->getCode())[0],
                    'Message' => $this->ResultMsg($e->getCode())[1],
                );
            }
        }

        return array(
            'code' => 400,
            'Title' => showText('Error_Page.400_title'),
        );
    }

    /**
     * 翻譯結果訊息
     * @param int $type 類型
     * @return string[] 訊息
     */
    private function ResultMsg(int $type): array {
        switch ($type) {
            case AUTH_CHANGESETTING_DATA_FAIL:
                return showText('ChangeSetting.AUTH_CHANGESETTING_DATA_FAIL');
            case AUTH_CHANGESETTING_DATA_FAIL_NOT_MATCH:
                return showText('ChangeSetting.AUTH_CHANGESETTING_DATA_FAIL_NOT_MATCH');
            case AUTH_CHANGESETTING_DATA_OK_EMAIL:
                return showText('ChangeSetting.AUTH_CHANGESETTING_DATA_OK_EMAIL');
            case AUTH_CHANGESETTING_DATA_OK:
                return showText('ChangeSetting.AUTH_CHANGESETTING_DATA_OK');
            case AUTH_CHANGESETTING_PASS_FAIL_EMPTY:
            case AUTH_CHANGESETTING_DATA_FAIL_EMPTY:
                return showText('ChangeSetting.AUTH_CHANGESETTING_PASS_FAIL_EMPTY');
            case AUTH_CHANGESETTING_PASS_OK:
                return showText('ChangeSetting.AUTH_CHANGESETTING_PASS_OK');
            case AUTH_CHANGESETTING_PASS_FAIL:
                return showText('ChangeSetting.AUTH_CHANGESETTING_PASS_FAIL');
            case AUTH_CHANGESETTING_PASS_FAIL_NOT_MATCH:
                return showText('ChangeSetting.AUTH_CHANGESETTING_PASS_FAIL_NOT_MATCH');
            case AUTH_CHANGESETTING_PASS_FAIL_NOT_STRONG:
                return showText('ChangeSetting.AUTH_CHANGESETTING_PASS_FAIL_NOT_STRONG');
            case AUTH_CHANGESETTING_PASS_FAIL_OLD_PASS_WRONG:
                return showText('ChangeSetting.AUTH_CHANGESETTING_PASS_FAIL_OLD_PASS_WRONG');
            case AUTH_CHANGESETTING_2FA_OFF_FAIL:
                return showText('ChangeSetting.AUTH_CHANGESETTING_2FA_OFF_FAIL');
            case AUTH_CHANGESETTING_2FA_OFF_OK:
                return showText('ChangeSetting.AUTH_CHANGESETTING_2FA_OFF_OK');
            case AUTH_CHANGESETTING_2FA_CHECK_CODE_FAIL:
                return showText('ChangeSetting.AUTH_CHANGESETTING_2FA_CHECK_CODE_FAIL');
            case AUTH_CHANGESETTING_2FA_CHECK_CODE_OK:
                return showText('ChangeSetting.AUTH_CHANGESETTING_2FA_CHECK_CODE_OK');
            case AUTH_CHANGESETTING_2FA_SHOWBACKUPCODE_FAIL:
                return showText('ChangeSetting.AUTH_CHANGESETTING_2FA_SHOWBACKUPCODE_FAIL');
            default:
                return array('', '');
        }
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