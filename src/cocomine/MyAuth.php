<?php
/*
 * Copyright (c) 2022.
 * Create by cocomine
 */

namespace cocomine;

use mysqli;
use Michael_Kliewe\GoogleAuthenticator\PHPGangsta_GoogleAuthenticator;
use RobThree\Auth\Providers\Qr\BaconQrCodeProvider;
use RobThree\Auth\TwoFactorAuth;
use RobThree\Auth\TwoFactorAuthException;

/* @deprecated */
define('AUTH_2FA_FORM_FUNC', 'TwoFA_form');
/* @deprecated */
define('AUTH_FORGETPASS_FORM_FUNC', 'ForgetPass_from');
/* @deprecated */
define('AUTH_FORGETPASS_SET_FORM_FUNC', 'ForgetPassSet_from');
define('AUTH_SERVER_ERROR', 2);

define('AUTH_NOT_DONE', 100);
define('AUTH_WRONG_PASS', 101);
define('AUTH_2FA_WRONG', 102);
define('AUTH_2FA_DUE', 103);
define('AUTH_BLOCK_10', 104);
define('AUTH_BLOCK_30', 105);
define('AUTH_GOOGLE_ERROR', 106);
define('AUTH_OK', 107);

define('AUTH_REGISTER_PASS_NOT_MATCH', 200);
define('AUTH_REGISTER_EMAIL_FAIL', 201);
define('AUTH_REGISTER_PASS_NOT_STRONG', 203);
define('AUTH_REGISTER_EMPTY', 204);
define('AUTH_REGISTER_EMAIL_WRONG_FORMAT', 205);
define('AUTH_REGISTER_LAST_STEP', 206);
define('AUTH_REGISTER_COMPLETE', 207);
define('AUTH_REGISTER_NAME_TOO_LONG', 208);
define('AUTH_REGISTER_YOUR_BOT', 210);
define('AUTH_REGISTER_CODE_WRONG', 211);

define('AUTH_MAIL_RESET', 300);
define('AUTH_MAIL_ACTIVATE', 301);
define('AUTH_MAIL_WONG_NEWIP', 302);

define('AUTH_FORGETPASS_EMAIL_FAIL', 400);
define('AUTH_FORGETPASS_LASTSTEP', 401);
define('AUTH_FORGETPASS_PASS_NOT_MATCH', 402);
define('AUTH_FORGETPASS_PASS_NOT_STRONG', 403);
define('AUTH_FORGETPASS_EMPTY', 404);
define('AUTH_FORGETPASS_COMPLETE', 405);
define('AUTH_FORGETPASS_CODE_WRONG', 406);
define('AUTH_FORGETPASS_YOUR_BOT', 407);
define('AUTH_FORGETPASS_CODE_OK', 408);

define('AUTH_CHANGESETTING_DATA', 500);
define('AUTH_CHANGESETTING_DATA_FAIL', 500.10);
define('AUTH_CHANGESETTING_DATA_FAIL_NOT_MATCH', 500.11);
define('AUTH_CHANGESETTING_DATA_OK_EMAIL', 500.20);
define('AUTH_CHANGESETTING_DATA_OK', 500.21);

define('AUTH_CHANGESETTING_PASS', 501);
define('AUTH_CHANGESETTING_PASS_OK', 501.10);
define('AUTH_CHANGESETTING_PASS_FAIL', 501.20);
define('AUTH_CHANGESETTING_PASS_FAIL_EMPTY', 501.21);
define('AUTH_CHANGESETTING_PASS_FAIL_NOT_MATCH', 501.22);
define('AUTH_CHANGESETTING_PASS_FAIL_NOT_STRONG', 501.23);
define('AUTH_CHANGESETTING_PASS_FAIL_OLD_PASS_WRONG', 501.24);

define('AUTH_CHANGESETTING_2FA', 502);
define('AUTH_CHANGESETTING_2FA_LOGON', 502.10);
define('AUTH_CHANGESETTING_2FA_CHECK_CODE', 503);
define('AUTH_CHANGESETTING_2FA_CHECK_CODE_OK', 503.10);
define('AUTH_CHANGESETTING_2FA_CHECK_CODE_FAIL', 503.20);
define('AUTH_CHANGESETTING_2FA_OFF_OK', 504);
define('AUTH_CHANGESETTING_2FA_OFF_FAIL', 504.10);
define('AUTH_CHANGESETTING_2FA_SHOWBACKUPCODE', 505);
define('AUTH_CHANGESETTING_2FA_SHOWBACKUPCODE_OK', 505.10);
define('AUTH_CHANGESETTING_2FA_SHOWBACKUPCODE_FAIL', 505.20);

/**
 * Class MyAuth
 * @package cocopixelmc\Auth
 */
class MyAuth {

    private string $ErrorFile = "";
    private string $CookiesPath = '/';
    public bool $islogin = false; //is login?
    public ?mysqli $sqlcon = null;
    public ?array $userdata = null;

    //sql setting
    private array $sqlsetting_User = array(
        'table' => 'User',
        'Email_col' => 'Email',
        'UUID_col' => 'UUID',
        'Password_col' => 'password',
        'Name_col' => 'Name',
        'activated_code_col' => 'activated_code',
        'activated_col' => 'activated',
        '2FA_col' => '2FA',
        '2FA_secret_col' => '2FA_secret',
        'Last_Login_col' => 'Last_Login',
        'Last_IP_col' => 'Last_IP',
        'Language_col' => 'Language',
        'role_col' => 'role'
    );
    private array $sqlsetting_IPBlock = array(
        'Last_time' => 'Last_time',
        'IP' => 'IP',
        'table' => 'Block_ip'
    );
    private array $sqlsetting_Forgetpass = array(
        'table' => 'ForgetPass',
        'UUID' => 'UUID',
        'Code' => 'Code',
        'Last_time' => 'Last_time'
    );
    private array $sqlsetting_TokeList = array(
        'table' => 'Toke_list',
        'Time' => 'Time',
        'UUID' => 'UUID',
        'Toke' => 'Toke',
        'IP' => 'IP'
    );
    private array $sqlsetting_2FA_BackupCode = array(
        'table' => '2FA_BackupCode',
        'UUID' => 'UUID',
        'Code' => 'Code',
        'used' => 'used'
    );
    private array $sqlsetting_Block_login_code = array(
        'table' => 'Block_login_code',
        'code' => 'code',
        'Toke' => 'Toke',
        'time' => 'time'
    );
    private array $Hook_func = array();

    /**
     * Auth class 初始化
     *
     * @param string $dbServer 數據庫IP
     * @param string $dbName 數據庫名稱
     * @param string $dbUser 數據庫用戶名稱
     * @param string $dbPass 數據庫密碼
     * @param string $ErrorFile 當出現錯誤時會展示的錯誤頁面php文件
     * @param string $CookiesPath 瀏覽器cookie規限路徑 預設: "/"
     */
    function __construct(string $dbServer = "localhost", string $dbName = "dbName", string $dbUser = "dbUser", string $dbPass = "dbPass", string $ErrorFile = '500ErrorFilePath', string $CookiesPath = '/') {
        @session_start();
        $this->sqlcon = new mysqli($dbServer, $dbUser, $dbPass, $dbName);
        if ($this->sqlcon->connect_errno) {
            header('HTTP/1.1 500 Internal Server Error');
            require_once($this->ErrorFile);
            exit();
        }

        $this->ErrorFile = $ErrorFile;
        $this->CookiesPath = $CookiesPath;
        $this->sqlcon->query("SET NAMES utf8");
    }

    /**
     * Google 登入
     * @param string $email 電郵
     * @return bool 註冊與否
     */
    function google_login(string $email): bool {
        //不能留空
        if (empty($email)) return false;

        /* 消毒 */
        $email = filter_var(trim($email), FILTER_SANITIZE_EMAIL);

        /* 查詢 */
        $stmt = $this->sqlcon->prepare("SELECT * FROM {$this->sqlsetting_User['table']} WHERE {$this->sqlsetting_User['Email_col']} = ?");
        $stmt->bind_param("s", $email);
        if (!$stmt->execute()) {
            ob_clean();
            http_response_code(500);
            require_once($this->ErrorFile);
            exit();
        }

        /* 分析結果 */
        $result = $stmt->get_result();
        $userdata = $result->fetch_assoc();
        $stmt->close();

        /* 檢查登入資訊 */
        if (mysqli_num_rows($result) < 1) return false;

        if (!$userdata[$this->sqlsetting_User['activated_col']]) return false;

        $toke = md5($this->Generate_Code());  //產生toke

        /* 更新最後時間 */
        $stmt = $this->sqlcon->prepare("UPDATE {$this->sqlsetting_User['table']} SET {$this->sqlsetting_User['Last_Login_col']} = UNIX_TIMESTAMP(), {$this->sqlsetting_User['Last_IP_col']} = ? WHERE {$this->sqlsetting_User['UUID_col']} = '{$userdata[$this->sqlsetting_User['UUID_col']]}'");
        $stmt->bind_param("s", $_SERVER['REMOTE_ADDR']);
        if (!$stmt->execute()) return false;
        $stmt->close();

        /* 2FA */
        /*if($userdata[$this -> sqlsetting_User['2FA_col']] == 1){
            $_SESSION['2FA']['Doing_2FA'] = true;
            $_SESSION['2FA']['toke'] = $toke;
            $_SESSION['2FA']['UUID'] = $userdata[$this -> sqlsetting_User['UUID_col']];
            $_SESSION['2FA']['userdata'] = $userdata;
            if(@$_POST['Remember_ME'] == 'on')
                $_SESSION['2FA']['Remember_ME'] = true;

            TwoFA_form(); //output form

        }*/

        /* 插入toke資料 */
        $stmt = $this->sqlcon->prepare("INSERT INTO {$this->sqlsetting_TokeList['table']} ({$this->sqlsetting_TokeList['UUID']}, {$this->sqlsetting_TokeList['IP']}, {$this->sqlsetting_TokeList['Toke']}, {$this->sqlsetting_TokeList['Time']}) VALUES (?, ?, ?, UNIX_TIMESTAMP())");
        $stmt->bind_param("sss", $userdata[$this->sqlsetting_User['UUID_col']], $_SERVER['REMOTE_ADDR'], $toke);
        if (!$stmt->execute()) return false;
        $stmt->close();

        /* 儲存 session */
        $_SESSION['UUID'] = $userdata[$this->sqlsetting_User['UUID_col']];
        $_SESSION['toke'] = $toke;
        setcookie('_ID', base64_encode($userdata[$this->sqlsetting_User['UUID_col']]), time() + 1209600, $this->CookiesPath, $_SERVER['HTTP_HOST'], true, true);

        $this->islogin = true; //login
        $this->Check_NewIP($userdata, $toke);

        return true;
    }

    /**
     * 登入帳戶
     *
     * @param string $email 電郵
     * @param string $password 密碼
     * @param bool $remember_me 記住我
     * @param string|null $RSAkey RSA加密
     * @return int 狀態
     */
    function login(string $email, string $password, bool $remember_me, string $RSAkey = null): int {

        //不能留空
        if (empty($email) || empty($password)) return AUTH_WRONG_PASS;

        /* 消毒 */
        $email = filter_var(trim($email), FILTER_SANITIZE_EMAIL);

        /* 防爆破 */
        $IPCheck = $this->Check_Block_ip($_SERVER['REMOTE_ADDR']);
        if ($IPCheck === AUTH_BLOCK_10) return AUTH_BLOCK_10;
        if ($IPCheck === AUTH_BLOCK_30) return AUTH_BLOCK_30;

        /* 解密RSA */
        if ($RSAkey != null) {
            $pi_key = openssl_pkey_get_private($RSAkey);
            $dePass = openssl_private_decrypt(base64_decode($password), $password, $pi_key);
            if (!$dePass) {
                $this->Add_Block_ip($_SERVER['REMOTE_ADDR']);
                return AUTH_WRONG_PASS;
            }
        }

        /* 加密 */
        $password = filter_var(trim($password), FILTER_SANITIZE_STRING); //消毒
        $password = 'Gblacklist' . $password;
        $password = hash('sha512', md5($password));

        /* 查詢 */
        $stmt = $this->sqlcon->prepare("SELECT * FROM {$this->sqlsetting_User['table']} WHERE {$this->sqlsetting_User['Email_col']} = ?");
        $stmt->bind_param("s", $email);
        if (!$stmt->execute()) return AUTH_SERVER_ERROR;

        /* 分析結果 */
        $result = $stmt->get_result();
        $userdata = $result->fetch_assoc();
        $stmt->close();

        /* 檢查登入資訊 */
        //檢查用戶存在
        if (mysqli_num_rows($result) < 1) {
            $this->Add_Block_ip($_SERVER['REMOTE_ADDR']);
            return AUTH_WRONG_PASS;
        }
        //檢查資料匹配
        if ($userdata[$this->sqlsetting_User['Email_col']] != $email || $userdata[$this->sqlsetting_User['Password_col']] != $password) {
            $this->Add_Block_ip($_SERVER['REMOTE_ADDR']);
            return AUTH_WRONG_PASS;
        }
        //檢查帳號啟動
        if (!$userdata[$this->sqlsetting_User['activated_col']]) return AUTH_NOT_DONE;

        $toke = md5($this->Generate_Code());  //產生toke

        /* 更新最後時間 */
        $stmt = $this->sqlcon->prepare("UPDATE {$this->sqlsetting_User['table']} SET {$this->sqlsetting_User['Last_Login_col']} = UNIX_TIMESTAMP(), {$this->sqlsetting_User['Last_IP_col']} = ? WHERE {$this->sqlsetting_User['UUID_col']} = '{$userdata[$this->sqlsetting_User['UUID_col']]}'");
        $stmt->bind_param("s", $_SERVER['REMOTE_ADDR']);
        if (!$stmt->execute()) return AUTH_SERVER_ERROR;
        $stmt->close();

        /* 2FA */
        /*if ($userdata[$this->sqlsetting_User['2FA_col']]) {
            $_SESSION['2FA']['Doing_2FA'] = true;
            $_SESSION['2FA']['toke'] = $toke;
            $_SESSION['2FA']['userdata'] = $userdata;
            $_SESSION['2FA']['Remember_ME'] = $_POST['Remember_ME'] ? true : false;
        }*/

        /* 插入toke資料 */
        $stmt = $this->sqlcon->prepare("INSERT INTO {$this->sqlsetting_TokeList['table']} ({$this->sqlsetting_TokeList['UUID']}, {$this->sqlsetting_TokeList['IP']}, {$this->sqlsetting_TokeList['Toke']}, {$this->sqlsetting_TokeList['Time']}) VALUES (?, ?, ?, UNIX_TIMESTAMP())");
        $stmt->bind_param("sss", $userdata[$this->sqlsetting_User['UUID_col']], $_SERVER['REMOTE_ADDR'], $toke);
        if (!$stmt->execute()) return AUTH_SERVER_ERROR;
        $stmt->close();

        /* 儲存 session */
        $_SESSION['UUID'] = $userdata[$this->sqlsetting_User['UUID_col']];
        $_SESSION['toke'] = $toke;
        setcookie('_ID', base64_encode($userdata[$this->sqlsetting_User['UUID_col']]), time() + 1209600, $this->CookiesPath, $_SERVER['HTTP_HOST'], true, true);

        $this->islogin = true; //login

        /* 記住我*/
        if ($remember_me) setcookie('_tk', base64_encode($toke), time() + 1209600, $this->CookiesPath, $_SERVER['HTTP_HOST'], true, true);

        return $this->Check_NewIP($userdata, $toke) ? AUTH_OK : AUTH_SERVER_ERROR;
    }

    /**
     * 封鎖登入
     * @param $code string 代碼
     * @return bool 是否成功
     */
    function Block_login(string $code): bool {
        /* 空值檢查 */
        if (empty($code)) return false;

        $code = filter_var(trim($code), FILTER_SANITIZE_STRING);

        /* 查詢 */
        $stmt = $this->sqlcon->prepare("SELECT * FROM {$this->sqlsetting_Block_login_code['table']} WHERE {$this->sqlsetting_Block_login_code['code']} = ?");
        $stmt->bind_param("s", $code);
        if (!$stmt->execute()) {
            ob_clean();
            header('HTTP/1.1 500 Internal Server Error');
            require_once($this->ErrorFile);
            exit();
        }

        /* 分析結果 */
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $stmt->close();

        /* 移除Toke */
        if (mysqli_num_rows($result) < 1) return false; //不存在else

        $stmt = $this->sqlcon->prepare("DELETE FROM {$this->sqlsetting_Block_login_code['table']} WHERE {$this->sqlsetting_Block_login_code['code']} = ?");
        $stmt->bind_param("s", $code);
        if (!$stmt->execute()) {
            ob_clean();
            header('HTTP/1.1 500 Internal Server Error');
            require_once($this->ErrorFile);
            exit();
        }
        $stmt->prepare("DELETE FROM {$this->sqlsetting_TokeList['table']} WHERE {$this->sqlsetting_TokeList['Toke']} = ?");
        $stmt->bind_param("s", $data[$this->sqlsetting_Block_login_code['Toke']]);
        if (!$stmt->execute()) {
            ob_clean();
            header('HTTP/1.1 500 Internal Server Error');
            require_once($this->ErrorFile);
            exit();
        }
        $stmt->close();

        return true; //存在
    }

    /**
     * 檢查最後登入ip
     * @param array $userdata User data
     * @param $toke string 用戶Toke
     * @return bool 是否檢查成功
     */
    private function Check_NewIP(array $userdata, string $toke): bool {
        /* 檢查最後登入ip */
        if ($userdata[$this->sqlsetting_User['Last_IP_col']] != $_SERVER['REMOTE_ADDR']) {
            /* 創建連接 */
            $code = md5((MyAuth . phpGenerate_Code()));
            $stmt = $this->sqlcon->prepare("INSERT INTO {$this->sqlsetting_Block_login_code['table']} ({$this->sqlsetting_Block_login_code['Toke']}, {$this->sqlsetting_Block_login_code['code']}, {$this->sqlsetting_Block_login_code['time']}) VALUES (?, ?, UNIX_TIMESTAMP())");
            $stmt->bind_param("ss", $toke, $code);
            if (!$stmt->execute()) return false;
            $stmt->close();

            $this->run_Hook('acc_Check_NewIP', true, $userdata, $code, $this->sqlcon);  //執行掛鉤
        } else {
            $this->run_Hook('acc_Check_NewIP', false); //執行掛鉤
        }
        return true;
    }

    /**
     * 防爆破
     *
     * @param string $ip 訪客ip
     */
    private function Add_Block_ip(string $ip) {
        /* 插入 */
        $stmt = $this->sqlcon->prepare("INSERT INTO {$this->sqlsetting_IPBlock['table']}({$this->sqlsetting_IPBlock['IP']}, {$this->sqlsetting_IPBlock['Last_time']}) VALUES (?, UNIX_TIMESTAMP())");
        $stmt->bind_param("s", $ip);
        if (!$stmt->execute()) {
            ob_clean();
            header('HTTP/1.1 500 Internal Server Error');
            require_once($this->ErrorFile);
            exit();
        }
    }

    /**
     * 防爆破
     *
     * @param string $ip ip地址
     * @return bool|int 狀態
     */
    private function Check_Block_ip(string $ip) {
        /* 插入 */
        $stmt = $this->sqlcon->prepare("SELECT COUNT({$this->sqlsetting_IPBlock['IP']}) AS 'count', (UNIX_TIMESTAMP()-MAX({$this->sqlsetting_IPBlock['Last_time']})) AS {$this->sqlsetting_IPBlock['Last_time']} FROM {$this->sqlsetting_IPBlock['table']} WHERE {$this->sqlsetting_IPBlock['IP']} = ?");
        $stmt->bind_param("s", $ip);
        if (!$stmt->execute()) {
            ob_clean();
            header('HTTP/1.1 500 Internal Server Error');
            require_once($this->ErrorFile);
            exit();
        }

        /* 分析結果 */
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $stmt->close();

        /* 判斷資訊 */
        if ($data['count'] == 5) { //5次
            if ($data[$this->sqlsetting_IPBlock['Last_time']] < 600) {
                return AUTH_BLOCK_10;
            } else {
                return true; //乜事都冇
            }
        } elseif ($data['count'] >= 10) { //多於十次
            if ($data[$this->sqlsetting_IPBlock['Last_time']] < 1800) {
                return AUTH_BLOCK_30;
            } else {
                return true; //乜事都冇
            }
        } else {
            return true; //乜事都冇
        }
    }

    /**
     * 2FA登入檢查
     *
     * @param string $code 確認代碼
     */
    function TwoFA_check(string $code) {
        $ga = new TwoFactorAuth();
        $code = filter_var(trim($code), FILTER_SANITIZE_STRING); //消毒

        /* 查詢 */
        $stmt = $this->sqlcon->prepare("SELECT {$this->sqlsetting_User['2FA_secret_col']}, (UNIX_TIMESTAMP()-{$this->sqlsetting_User['Last_Login_col']}) AS {$this->sqlsetting_User['Last_Login_col']}, {$this->sqlsetting_User['Language_col']}, {$this->sqlsetting_User['Last_IP_col']} FROM {$this->sqlsetting_User['table']} WHERE {$this->sqlsetting_User['UUID_col']} = ?");
        $stmt->bind_param("s", $_SESSION['2FA']['UUID']);
        if (!$stmt->execute()) {
            ob_clean();
            header('HTTP/1.1 500 Internal Server Error');
            require_once($this->ErrorFile);
            exit();
        }

        /* 分析結果 */
        $result = $stmt->get_result();
        $userdata = $result->fetch_assoc();
        $stmt->close();

        /* 檢查代碼 */
        if ($userdata[$this->sqlsetting_User['Last_Login_col']] <= 600) {
            if (!empty($code)) { //不能留空
                if ($ga->verifyCode($userdata[$this->sqlsetting_User['2FA_secret_col']], $code)) {  //檢查 2FA Code
                    /* 使用代碼 */

                    /* 插入toke資料 */
                    $stmt = $this->sqlcon->prepare("INSERT INTO {$this->sqlsetting_TokeList['table']} ({$this->sqlsetting_TokeList['UUID']}, {$this->sqlsetting_TokeList['IP']}, {$this->sqlsetting_TokeList['Toke']}, {$this->sqlsetting_TokeList['Time']}) VALUES (?, ?, ?, UNIX_TIMESTAMP())");
                    $stmt->bind_param("sss", $_SESSION['2FA']['UUID'], $_SERVER['REMOTE_ADDR'], $_SESSION['2FA']['toke']);
                    if (!$stmt->execute()) {
                        ob_clean();
                        header('HTTP/1.1 500 Internal Server Error');
                        require_once($this->ErrorFile);
                        exit();
                    }
                    $stmt->close();

                    $_SESSION['toke'] = $_SESSION['2FA']['toke']; //Set toke
                    $_SESSION['UUID'] = $_SESSION['2FA']['UUID']; //Set uuid

                    /* 記住我功能 */
                    if (@$_SESSION['2FA']['Remember_ME']) {
                        setcookie('_ID', base64_encode($_SESSION['2FA']['UUID']), time() + 1209600, $this->CookiesPath, $_SERVER['HTTP_HOST'], true, true);
                        setcookie('_tk', base64_encode($_SESSION['2FA']['toke']), time() + 1209600, $this->CookiesPath, $_SERVER['HTTP_HOST'], true, true);
                    }

                    $this->islogin = true; //login
                    setcookie('Lang', $userdata[$this->sqlsetting_User['Language_col']], time() + 2592000, $this->CookiesPath, $_SERVER['HTTP_HOST'], true); //設置語言cookies

                    $this->Check_NewIP($_SESSION['2FA']['userdata'], $_SESSION['toke']);
                    unset($_SESSION['2FA']);

                } else {
                    /* 使用安全代碼 */

                    $stmt = $this->sqlcon->prepare("SELECT {$this->sqlsetting_2FA_BackupCode['Code']}, {$this->sqlsetting_2FA_BackupCode['used']} FROM {$this->sqlsetting_2FA_BackupCode['table']} WHERE {$this->sqlsetting_2FA_BackupCode['UUID']} = ?");
                    $stmt->bind_param("s", $_SESSION['2FA']['UUID']);
                    if (!$stmt->execute()) {
                        ob_clean();
                        header('HTTP/1.1 500 Internal Server Error');
                        require_once($this->ErrorFile);
                        exit();
                    }

                    /* 分析結果 */
                    $result = $stmt->get_result();
                    $stmt->close();

                    while ($row = $result->fetch_assoc()) {
                        /* 檢查安全代碼 */
                        if ($row[$this->sqlsetting_2FA_BackupCode['Code']] == $code && $row[$this->sqlsetting_2FA_BackupCode['used']] == false) {

                            /* 插入toke資料 */
                            $stmt = $this->sqlcon->prepare("INSERT INTO {$this->sqlsetting_TokeList['table']} ({$this->sqlsetting_TokeList['UUID']}, {$this->sqlsetting_TokeList['IP']}, {$this->sqlsetting_TokeList['Toke']}, {$this->sqlsetting_TokeList['Time']}) VALUES (?, ?, ?, UNIX_TIMESTAMP())");
                            $stmt->bind_param("sss", $_SESSION['2FA']['UUID'], $_SERVER['REMOTE_ADDR'], $_SESSION['2FA']['toke']);
                            if (!$stmt->execute()) {
                                ob_clean();
                                header('HTTP/1.1 500 Internal Server Error');
                                require_once($this->ErrorFile);
                                exit();
                            }

                            $_SESSION['toke'] = $_SESSION['2FA']['toke']; //Set toke
                            $_SESSION['UUID'] = $_SESSION['2FA']['UUID']; //Set uuid

                            $stmt->prepare("UPDATE {$this->sqlsetting_2FA_BackupCode['table']} SET {$this->sqlsetting_2FA_BackupCode['used']} = true WHERE {$this->sqlsetting_2FA_BackupCode['UUID']} = ? AND {$this->sqlsetting_2FA_BackupCode['Code']} = ?");
                            $stmt->bind_param("ss", $_SESSION['2FA']['UUID'], $code);
                            if (!$stmt->execute()) {
                                ob_clean();
                                header('HTTP/1.1 500 Internal Server Error');
                                require_once($this->ErrorFile);
                                exit();
                            }
                            $stmt->close();

                            /* 記住我功能 */
                            if (@$_SESSION['2FA']['Remember_ME']) {
                                setcookie('_ID', base64_encode($_SESSION['2FA']['UUID']), time() + 1209600, $this->CookiesPath, $_SERVER['HTTP_HOST'], true, true);
                                setcookie('_tk', base64_encode($_SESSION['2FA']['toke']), time() + 1209600, $this->CookiesPath, $_SERVER['HTTP_HOST'], true, true);
                            }

                            $this->islogin = true; //login
                            setcookie('Lang', $userdata[$this->sqlsetting_User['Language_col']], time() + 2592000, $this->CookiesPath, $_SERVER['HTTP_HOST'], true); //設置語言cookies

                            $this->Check_NewIP($_SESSION['2FA']['userdata'], $_SESSION['toke']);
                            unset($_SESSION['2FA']);
                            return; //End Check
                        }
                    }
                    call_user_func(AUTH_2FA_FORM_FUNC, AUTH_2FA_WRONG);

                }
            } else {
                call_user_func(AUTH_2FA_FORM_FUNC, AUTH_2FA_WRONG);
            }
        } else {
            call_user_func(AUTH_2FA_FORM_FUNC, AUTH_2FA_DUE);
            unset($_SESSION['2FA']);
        }
    }

    /**
     * 登出帳號
     */
    function logout() {
        if (empty($_SESSION['UUID'])) return;

        /* 移除toke紀錄 */
        $stmt = $this->sqlcon->prepare("DELETE FROM {$this->sqlsetting_TokeList['table']} WHERE {$this->sqlsetting_TokeList['Toke']} = ?");
        $stmt->bind_param("s", $_SESSION['toke']);
        if (!$stmt->execute()) {
            ob_clean();
            http_response_code(500);
            require_once($this->ErrorFile);
            exit();
        }
        $stmt->close();

        unset($_SESSION['UUID']);
        unset($_SESSION['toke']);
        setcookie('_ID', 'uuid', time() - 3600, $this->CookiesPath, $_SERVER['HTTP_HOST'], true, true);

        /* 使用了記住我 */
        if (!empty($_COOKIE['_tk'])) setcookie('_tk', 'toke', time() - 3600, $this->CookiesPath, $_SERVER['HTTP_HOST'], true, true);
    }

    /**
     * 檢查登入狀態
     */
    function checkAuth(): void {
        /* 如果Doing_2FA處於true的時候不進行登入動作 */
        if (@$_SESSION['2FA']['Doing_2FA']) return;

        /* 查詢 SQL
         * SELECT User.*, IF(Toke_list.Toke = ?, TRUE , FALSE) AS 'TrueToke'
         * FROM User AS User, Toke_list AS Toke_list
         * WHERE Toke_list.UUID = User.UUID
         *      AND Toke_list.Toke = ?
         * LIMIT 1
         */
        $query = "SELECT {$this->sqlsetting_User['table']}.*, IF({$this->sqlsetting_TokeList['table']}.{$this->sqlsetting_TokeList['Toke']} = ?, TRUE , FALSE) AS 'TrueToke' FROM {$this->sqlsetting_User['table']} AS User, {$this->sqlsetting_TokeList['table']} AS Toke_list WHERE Toke_list.{$this->sqlsetting_TokeList['UUID']} = User.{$this->sqlsetting_User['UUID_col']} AND Toke_list.{$this->sqlsetting_TokeList['Toke']} = ? LIMIT 1";

        /* 系統資訊優先 */
        if (!empty($_SESSION['UUID']) && !empty($_SESSION['toke'])) {

            /* 查詢 */
            $stmt = $this->sqlcon->prepare($query);
            $stmt->bind_param("ss", $_SESSION['toke'], $_SESSION['toke']);
            if (!$stmt->execute()) {
                ob_clean();
                http_response_code(500);
                require_once($this->ErrorFile);
                exit();
            }

            /* 分析結果 */
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $this->userdata = array(
                'UUID' => $row[$this->sqlsetting_User['UUID_col']],
                'Email' => $row[$this->sqlsetting_User['Email_col']],
                'Activated' => $row[$this->sqlsetting_User['activated_col']],
                'Name' => $row[$this->sqlsetting_User['Name_col']],
                'Role' => $row[$this->sqlsetting_User['role_col']],
                'Language' => $row[$this->sqlsetting_User['Language_col']],
                'TrueToke' => $row['TrueToke'],
                'ALLData' => $row
            );
            $stmt->close();

            /* 檢查登入資訊 */
            if (mysqli_num_rows($result) < 1) return;

            if (!($_SESSION['UUID'] == $this->userdata['UUID'] && $this->userdata['TrueToke'])) return;

            //Login!
            $this->islogin = true;
            if (empty($_COOKIE['Lang'])) setLang($this->userdata['Language']);
            setcookie('Lang', $this->userdata['Language'], time() + 2592000, $this->CookiesPath, $_SERVER['HTTP_HOST'], true); //設置語言cookies
        } else

            /* 記住我登入 */
            if (!empty($_COOKIE['_tk']) && !empty($_COOKIE['_ID'])) {

                /* 消毒/解密 */
                $uuid = base64_decode($_COOKIE['_ID']);
                $uuid = filter_var($uuid, FILTER_SANITIZE_STRING);
                $toke = base64_decode($_COOKIE['_tk']);
                $toke = filter_var($toke, FILTER_SANITIZE_STRING);

                /* 查詢 */
                $stmt = $this->sqlcon->prepare($query);
                $stmt->bind_param("ss", $toke, $toke);
                if (!$stmt->execute()) {
                    ob_clean();
                    http_response_code(500);
                    require_once($this->ErrorFile);
                    exit();
                }

                /* 分析結果 */
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                $this->userdata = array(
                    'UUID' => $row[$this->sqlsetting_User['UUID_col']],
                    'Email' => $row[$this->sqlsetting_User['Email_col']],
                    'Activated' => $row[$this->sqlsetting_User['activated_col']],
                    'Name' => $row[$this->sqlsetting_User['Name_col']],
                    'Role' => $row[$this->sqlsetting_User['role_col']],
                    'Language' => $row[$this->sqlsetting_User['Language_col']],
                    'TrueToke' => $row['TrueToke'],
                    'ALLData' => $row
                );
                $stmt->close();

                /* 檢查登入資訊, 不符合取消cookie */
                if (mysqli_num_rows($result) < 1 || !($uuid == $this->userdata['UUID'] && $this->userdata['TrueToke'] && $this->userdata['Activated'])) {
                    setcookie('_ID', 'uuid', time() - 3600, $this->CookiesPath, $_SERVER['HTTP_HOST'], true, true);
                    setcookie('_tk', 'toke', time() - 3600, $this->CookiesPath, $_SERVER['HTTP_HOST'], true, true);
                    return;
                }

                //Login!
                $_SESSION['UUID'] = $this->userdata['UUID'];
                $_SESSION['toke'] = $toke;

                $this->islogin = true;
                if (empty($_COOKIE['Lang'])) setLang($this->userdata['Language']);
                setcookie('Lang', $this->userdata['Language'], time() + 1209600, $this->CookiesPath, $_SERVER['HTTP_HOST'], true); //設置語言cookies
            }
    }

    /**
     * 註冊帳號
     *
     * @param $Name String 用戶名稱
     * @param $Cpass String 確認密碼
     * @param $Pass String 密碼
     * @param $Email String 電郵地址
     * @param $localCode String 語言代號
     * @param string $recaptcha 防止機械人
     * @param string $recaptcha_key 防止機械人API KEY
     * @param string|null $RSAKey RSA加密
     * @return int 處理狀態
     */
    function register(string $Name, string $Cpass, string $Pass, string $Email, string $localCode, string $recaptcha, string $recaptcha_key, string $RSAKey = null): int {

        //不能留空
        if (empty($Cpass) || empty($Name) || empty($Pass) || empty($Email) || empty($recaptcha)) return AUTH_REGISTER_EMPTY;

        /* 檢查是否機械人 */
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, "https://www.google.com/recaptcha/api/siteverify");
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, "secret={$recaptcha_key}&response={$recaptcha}"); //will add ip
        $output = curl_exec($curl);
        $json = json_decode($output);
        //print_r($json);

        /* 解密 */
        if ($RSAKey != null) {
            $pi_key = openssl_pkey_get_private($RSAKey);
            $dePass = openssl_private_decrypt(base64_decode($Pass), $Pass, $pi_key);
            $deCpass = openssl_private_decrypt(base64_decode($Cpass), $Cpass, $pi_key);
            //解密失敗
            if (!($dePass && $deCpass)) return AUTH_REGISTER_PASS_NOT_MATCH;
        }

        /* 消毒 */
        $email = filter_var(trim($Email), FILTER_SANITIZE_EMAIL);
        $name = filter_var(trim($Name), FILTER_SANITIZE_STRING);
        $Cpass = filter_var(trim($Cpass), FILTER_SANITIZE_STRING);
        $Pass = filter_var(trim($Pass), FILTER_SANITIZE_STRING);

        /* 進入判斷程序 */
        //是否機械人
        if (!($json->success && $json->hostname == $_SERVER['SERVER_NAME'])) return AUTH_REGISTER_YOUR_BOT;
        //檢查名稱是否少於16字
        if (strlen($Name) > 16) return AUTH_REGISTER_NAME_TOO_LONG;
        //檢查密碼是否一樣
        if ($Pass != $Cpass) return AUTH_REGISTER_PASS_NOT_MATCH;

        /* 判斷密碼強度 */
        if (!(preg_match("/(?=.*?[A-Z])(?=.*?[a-z]).{8,}/", $Cpass) && $Name != $Cpass && $Email != $Cpass)) return AUTH_REGISTER_PASS_NOT_STRONG;

        /* 加密 */
        $password = 'Gblacklist' . $Cpass;
        $password = hash('sha512', md5($password));

        //檢查電郵格式
        if (!preg_match("/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/", $email)) return AUTH_REGISTER_EMAIL_WRONG_FORMAT;

        /* 產生啟動序號 */
        $ActivatedCode = Generate_Code(16);

        /* 增值資料 & 檢查電郵衝突 & 取得UUID*/
        $stmt = $this->sqlcon->prepare("SET @uuid = UUID();"); //設置UUID
        $stmt->execute();
        $stmt->prepare("INSERT INTO {$this->sqlsetting_User['table']} ({$this->sqlsetting_User['UUID_col']}, {$this->sqlsetting_User['Email_col']}, {$this->sqlsetting_User['Name_col']}, {$this->sqlsetting_User['Password_col']}, {$this->sqlsetting_User['activated_code_col']}, {$this->sqlsetting_User['Last_Login_col']}, {$this->sqlsetting_User['Last_IP_col']}, {$this->sqlsetting_User['Language_col']}) VALUES (@uuid, ?, ?, ?, ?, UNIX_TIMESTAMP(), ?, ?)"); //加入資料
        $stmt->bind_param("ssssss", $email, $name, $password, $ActivatedCode, $_SERVER['REMOTE_ADDR'], $localCode);
        if ($stmt->execute()) {
            $stmt->prepare("SELECT @uuid AS UUID"); //取得UUID
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $uuid = $row['UUID'];
            $stmt->close();

            $this->run_Hook('acc_register', $uuid, $email, $ActivatedCode, $name, $this->sqlcon); //執行Hook
            return AUTH_REGISTER_LAST_STEP; //Done
        } else {
            $stmt->close();
            return AUTH_REGISTER_EMAIL_FAIL;
        }
    }

    /**
     * 激活帳號
     *
     * @param string $code 啟動碼
     */
    function activated(string $code): int {
        //不能留空
        if (empty($code)) return AUTH_REGISTER_CODE_WRONG;

        /* 解碼 */
        $code = base64_decode($code);
        $code = filter_var($code, FILTER_SANITIZE_STRING);
        $code = mb_split("@", $code);

        /* 查詢 */
        $stmt = $this->sqlcon->prepare("SELECT {$this->sqlsetting_User['activated_code_col']}, (UNIX_TIMESTAMP()-{$this->sqlsetting_User['Last_Login_col']}) AS {$this->sqlsetting_User['Last_Login_col']}, {$this->sqlsetting_User['activated_col']} FROM {$this->sqlsetting_User['table']} WHERE {$this->sqlsetting_User['UUID_col']} = ? LIMIT 1;");
        $stmt->bind_param('s', $code[0]);
        if (!$stmt->execute()) return AUTH_SERVER_ERROR;

        /* 分析結果 */
        $result = $stmt->get_result();
        $activated_code = $result->fetch_assoc();
        $stmt->close();

        /* 檢查 */
        if (mysqli_num_rows($result) < 1) return AUTH_REGISTER_CODE_WRONG;
        if ($activated_code[$this->sqlsetting_User['activated_code_col']] !== $code[1] || $activated_code[$this->sqlsetting_User['Last_Login_col']] > 86400) return AUTH_REGISTER_CODE_WRONG;

        /* 更新資料 */
        $stmt = $this->sqlcon->prepare("UPDATE {$this->sqlsetting_User['table']} SET {$this->sqlsetting_User['activated_col']} = true, {$this->sqlsetting_User['activated_code_col']} = null WHERE {$this->sqlsetting_User['UUID_col']} = ?");
        $stmt->bind_param('s', $code[0]);
        if (!$stmt->execute()) return AUTH_SERVER_ERROR;

        $this->run_Hook('acc_activated', $code[0], $this->sqlcon); //執行掛鉤
        return AUTH_REGISTER_COMPLETE;
    }

    /**
     *更改個人檔案<br/>
     * **$data參數輸入資料array key:**
     * + AUTH_CHANGESETTING_DATA  *改資料*
     *      + Name -> 用戶名稱
     *      + Email -> 電郵地址
     *      + Lang -> 語言
     * + AUTH_CHANGESETTING_PASS  *換密碼(直接輸入加密資料)*
     *      + NewPass -> 新密碼
     *      + ConfirmPass -> 確認新密碼
     *      + OldPass -> 舊密碼
     * + AUTH_CHANGESETTING_2FA  *2FA開啟關閉*
     *      + 2FAto -> 更改2FA狀態  *true: 開啟, false: 關閉*
     * + AUTH_CHANGESETTING_2FA_CHECK_CODE  *2FA檢查代碼是否正確*
     *      + code -> 2FA Code
     *
     * @param int $type 修改類型
     * @param array $data 修改資料
     * @param bool $RSAon 是否使用了RSA加密
     * @return array 狀態
     */
    function changeSetting(int $type, array $data, bool $RSAon = true): ?array {

        /* 修改資料 */
        if ($type === AUTH_CHANGESETTING_DATA) {

            /* 消毒 */
            $data['Email'] = filter_var(trim($data['Email']), FILTER_SANITIZE_EMAIL);
            $data['Name'] = filter_var(trim($data['Name']), FILTER_SANITIZE_STRING);
            $data['Lang'] = filter_var(trim($data['Lang']), FILTER_SANITIZE_STRING);

            /* 未定義/空值檢查 */
            if (empty($data['Name']) || empty($data['Email']) || empty($data['Lang'])) {
                return array(AUTH_CHANGESETTING_DATA_FAIL);
            }

            /* 檢查電郵是否更改 */
            if (strlen($data['Name']) > 16 || !preg_match("/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/", $data['Email'])) {
                return array(AUTH_CHANGESETTING_DATA_FAIL_NOT_MATCH);
            } else
                if ($data['Email'] !== $this->userdata[$this->sqlsetting_User['Email_col']]) {

                    /* 更改電郵 */
                    /* 產生啟動序號 */
                    $ActivatedCode = Generate_Code(16);

                    /* 修改資料 */
                    $stmt = $this->sqlcon->prepare("UPDATE {$this->sqlsetting_User['table']} SET {$this->sqlsetting_User['Name_col']} = ?, {$this->sqlsetting_User['Email_col']} = ?, {$this->sqlsetting_User['activated_col']} = false, {$this->sqlsetting_User['activated_code_col']} = ?, {$this->sqlsetting_User['Language_col']} = ? WHERE {$this->sqlsetting_User['UUID_col']} = ?");
                    $stmt->bind_param("sssss", $data['Name'], $data['Email'], $ActivatedCode, $data['Lang'], $_SESSION['UUID']);
                    if (!$stmt->execute()) {
                        $stmt->close();
                        return array(AUTH_CHANGESETTING_DATA_FAIL);
                    }
                    $stmt->close();

                    //Send Mail
                    $this->run_Hook('acc_changeSetting_data', $_SESSION['UUID'], $data['Email'], $ActivatedCode, $data['Name'], $this->sqlcon); //執行Hook
                    return array(AUTH_CHANGESETTING_DATA_OK_EMAIL, $data['Name']);

                } else {

                    /* Only修改名稱 */
                    $stmt = $this->sqlcon->prepare("UPDATE {$this->sqlsetting_User['table']} SET {$this->sqlsetting_User['Name_col']} = ?, {$this->sqlsetting_User['Language_col']} = ? WHERE {$this->sqlsetting_User['UUID_col']} = ?");
                    $stmt->bind_param("sss", $data['Name'], $data['Lang'], $_SESSION['UUID']);
                    if (!$stmt->execute()) {
                        $stmt->close();
                        return array(AUTH_CHANGESETTING_DATA_FAIL);
                    }
                    $stmt->close();
                    return array(AUTH_CHANGESETTING_DATA_OK, $data['Name']);
                }

        } elseif ($type === AUTH_CHANGESETTING_PASS) {
            if ((!empty($data['NewPass']) && !empty($data['ConfirmPass'])) && !empty($data['OldPass'])) { ////不能留空

                /* 解密 */
                $pi_key = openssl_pkey_get_private($_SESSION['pvKey']);
                if ((openssl_private_decrypt(base64_decode($data['NewPass']), $data['NewPass'], $pi_key) && //解密
                        openssl_private_decrypt(base64_decode($data['ConfirmPass']), $data['ConfirmPass'], $pi_key) &&
                        openssl_private_decrypt(base64_decode($data['OldPass']), $data['OldPass'], $pi_key)) || !$RSAon) {

                    /* 消毒 */
                    $data['OldPass'] = filter_var(trim($data['OldPass']), FILTER_SANITIZE_STRING);
                    $data['ConfirmPass'] = filter_var(trim($data['ConfirmPass']), FILTER_SANITIZE_STRING);
                    $data['NewPass'] = filter_var(trim($data['NewPass']), FILTER_SANITIZE_STRING);

                    if ($data['NewPass'] === $data['ConfirmPass']) { //檢查密碼是否一樣
                        //判斷密碼強度
                        if (preg_match("/(?=.*?[A-Z])(?=.*?[a-z])/", $data['ConfirmPass']) && strlen($data['ConfirmPass']) >= 8 && $this->userdata[$this->sqlsetting_User['Name_col']] !== $data['ConfirmPass'] && $this->userdata[$this->sqlsetting_User['Email_col']] !== $data['ConfirmPass']) {

                            /* 加密 */
                            $password = 'Gblacklist' . $data['ConfirmPass'];
                            $password = hash('sha512', md5($password));
                            $OLDpassword = 'Gblacklist' . $data['OldPass'];
                            $OLDpassword = hash('sha512', md5($OLDpassword));

                            if ($OLDpassword == $this->userdata['ALLData'][$this->sqlsetting_User['Password_col']]) {
                                /* 修改資料 */
                                $stmt = $this->sqlcon->prepare("UPDATE {$this->sqlsetting_User['table']} SET {$this->sqlsetting_User['Password_col']} = ? WHERE {$this->sqlsetting_User['UUID_col']} = ?");
                                $stmt->bind_param('ss', $password, $_SESSION['UUID']);
                                if (!$stmt->execute()) {
                                    $stmt->close();
                                    return array(AUTH_CHANGESETTING_PASS_FAIL);
                                }

                                return array(AUTH_CHANGESETTING_PASS_OK); //成功
                            } else {
                                return array(AUTH_CHANGESETTING_PASS_FAIL_OLD_PASS_WRONG);
                            }
                        } else {
                            return array(AUTH_CHANGESETTING_PASS_FAIL_NOT_STRONG);
                        }
                    } else {
                        return array(AUTH_CHANGESETTING_PASS_FAIL_NOT_MATCH);
                    }
                } else {
                    return array(AUTH_CHANGESETTING_PASS_FAIL);
                }
            } else {
                return array(AUTH_CHANGESETTING_PASS_FAIL_EMPTY);
            }

        } elseif ($type === AUTH_CHANGESETTING_2FA) {

            try {
                $qrProvider = new BaconQrCodeProvider();
                $G2AF = new TwoFactorAuth(null, 6, 30, 'sha1', $qrProvider);

                if ($data['2FAto']) {
                    /* 啟動 */
                    $secret = $G2AF->createSecret();
                    $qr = $G2AF->getQRCodeImageAsDataUri($this->userdata['Name'], $secret);
                    $_SESSION['2FA_secret'] = $secret;
                    return array(
                        0 => AUTH_CHANGESETTING_2FA_LOGON,
                        1 => $secret,
                        2 => $qr
                    );
                } else {
                    /* 關閉 */
                    $stmt = $this->sqlcon->prepare("UPDATE {$this->sqlsetting_User['table']} SET {$this->sqlsetting_User['2FA_col']} = FALSE, {$this->sqlsetting_User['2FA_secret_col']} = NULL WHERE {$this->sqlsetting_User['UUID_col']} = ?");
                    $stmt->bind_param('s', $this->userdata['UUID']);
                    if (!$stmt->execute()) {
                        $stmt->close();
                        return array(AUTH_CHANGESETTING_2FA_OFF_FAIL); //Fail
                    }
                    $stmt->close();

                    //刪除重設代碼
                    $stmt = $this->sqlcon->prepare("DELETE FROM {$this->sqlsetting_2FA_BackupCode['table']} WHERE {$this->sqlsetting_2FA_BackupCode['UUID']} = ?");
                    $stmt->bind_param('s', $this->userdata['UUID']);
                    if (!$stmt->execute()) {
                        $stmt->close();
                        return array(AUTH_CHANGESETTING_2FA_OFF_FAIL); //Fail
                    }
                    $stmt->close();

                    return array(AUTH_CHANGESETTING_2FA_OFF_OK); //OK
                }

            } catch (TwoFactorAuthException $e) {
                return array(AUTH_CHANGESETTING_2FA_OFF_FAIL); //Fail
            }

        } elseif ($type === AUTH_CHANGESETTING_2FA_CHECK_CODE) {
            /* 認證開啟 */
            $G2AF = new TwoFactorAuth();

            /* 消毒 */
            $data['code'] = filter_var(trim($data['code']), FILTER_SANITIZE_EMAIL);

            /* 確認code */
            if ($G2AF->verifyCode($_SESSION['2FA_secret'], $data['code'])) {
                $stmt = $this->sqlcon->prepare("UPDATE {$this->sqlsetting_User['table']} SET {$this->sqlsetting_User['2FA_col']} = TRUE, {$this->sqlsetting_User['2FA_secret_col']} = ? WHERE {$this->sqlsetting_User['UUID_col']} = ?");
                $stmt->bind_param('ss', $_SESSION['2FA_secret'], $this->userdata['UUID']);
                if (!$stmt->execute()) {
                    $stmt->close();
                    return array(AUTH_CHANGESETTING_2FA_CHECK_CODE_FAIL); //Fail
                }
                $stmt->close();

                /* 產出後備代碼 */
                for ($i = 0; $i < 12; $i++) {
                    $stmt = $this->sqlcon->prepare("INSERT INTO {$this->sqlsetting_2FA_BackupCode['table']} ({$this->sqlsetting_2FA_BackupCode['UUID']}, {$this->sqlsetting_2FA_BackupCode['Code']}) VALUES (?, ?)");
                    $generate_Code = Generate_Code(6);
                    $stmt->bind_param('ss', $this->userdata[$this->sqlsetting_User['UUID_col']], $generate_Code);
                    if (!$stmt->execute()) {
                        $stmt->close();
                        return array(AUTH_CHANGESETTING_2FA_CHECK_CODE_FAIL); //Fail
                    }
                    $stmt->close();
                }

                return array(AUTH_CHANGESETTING_2FA_CHECK_CODE_OK); //OK
            } else {
                return array(AUTH_CHANGESETTING_2FA_CHECK_CODE_FAIL); //Fail
            }
        } elseif ($type === AUTH_CHANGESETTING_2FA_SHOWBACKUPCODE) {
            /* 查詢資料 */
            $stmt = $this->sqlcon->prepare("SELECT {$this->sqlsetting_2FA_BackupCode['Code']}, {$this->sqlsetting_2FA_BackupCode['used']} FROM {$this->sqlsetting_2FA_BackupCode['table']} WHERE {$this->sqlsetting_2FA_BackupCode['UUID']} = ?");
            $stmt->bind_param('s', $this->userdata['UUID']);
            if (!$stmt->execute()) {
                return array(AUTH_CHANGESETTING_2FA_SHOWBACKUPCODE_FAIL);
            }
            $result = $stmt->get_result();

            /* 分析資料 */
            $ALLcode = array();
            while ($row = $result->fetch_assoc()) {
                array_push($ALLcode, $row);
            }
            $stmt->close();

            return array(AUTH_CHANGESETTING_2FA_SHOWBACKUPCODE_OK, $ALLcode);
        }
        return null;
    }

    /**
     * 確認代碼修改密碼
     * @param string $code
     * @return int 狀態
     */
    function ForgetPass_Confirm(string $code): int {
        /* 解碼 */
        $code = base64_decode($code);
        $code = filter_var($code, FILTER_SANITIZE_STRING);
        $code = mb_split("@", $code);

        /* 查詢 */
        $stmt = $this->sqlcon->prepare("SELECT {$this->sqlsetting_Forgetpass['Code']}, (UNIX_TIMESTAMP()-{$this->sqlsetting_Forgetpass['Last_time']}) AS {$this->sqlsetting_Forgetpass['Last_time']} FROM {$this->sqlsetting_Forgetpass['table']} WHERE {$this->sqlsetting_Forgetpass['UUID']} = ? LIMIT 1;");
        $stmt->bind_param('s', $code[0]);
        if (!$stmt->execute()) return AUTH_SERVER_ERROR;

        /* 分析結果 */
        $result = $stmt->get_result();
        $userdata = $result->fetch_assoc();
        $stmt->close();

        /* 檢查 */
        if (mysqli_num_rows($result) < 1) return AUTH_FORGETPASS_CODE_WRONG;

        if ($userdata[$this->sqlsetting_Forgetpass['Code']] != $code[1] || $userdata[$this->sqlsetting_Forgetpass['Last_time']] > 3600) return AUTH_FORGETPASS_CODE_WRONG;

        /* 移除條目 */
        $stmt = $this->sqlcon->prepare("DELETE FROM {$this->sqlsetting_Forgetpass['table']} WHERE {$this->sqlsetting_Forgetpass['UUID']} = ?;");
        $stmt->bind_param('s', $code[0]);
        if (!$stmt->execute()) return AUTH_SERVER_ERROR;

        /* 回饋成功 */
        $_SESSION['Auth']['uuid'] = $code[0];
        return AUTH_FORGETPASS_CODE_OK;

    }

    /**
     * 忘記密碼, 輸入電郵尋找
     * @param string $email 電郵地址
     * @param string $recaptcha 防止機械人
     * @param string $recaptcha_key 防止機械人API KEY
     */
    function ForgetPass(string $email, string $recaptcha, string $recaptcha_key): int {
        //不能留空
        if (empty($email) || empty($recaptcha)) return AUTH_FORGETPASS_EMPTY;

        /* 檢查是否機械人 */
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, "https://www.google.com/recaptcha/api/siteverify");
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, "secret={$recaptcha_key}&response={$recaptcha}"); //will add ip
        $output = curl_exec($curl);
        $json = json_decode($output);
        //print_r($json);

        if (!($json->success && $json->hostname == $_SERVER['SERVER_NAME'])) return AUTH_FORGETPASS_YOUR_BOT;

        /* 消毒 */
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);

        /* 查詢 */
        $stmt = $this->sqlcon->prepare("SELECT {$this->sqlsetting_User['UUID_col']}, {$this->sqlsetting_User['Name_col']}, {$this->sqlsetting_User['Language_col']} FROM {$this->sqlsetting_User['table']} WHERE {$this->sqlsetting_User['Email_col']} = ?");
        $stmt->bind_param("s", $email);
        if (!$stmt->execute()) return AUTH_SERVER_ERROR;

        /* 分析結果 */
        $result = $stmt->get_result();
        $userdata = $result->fetch_assoc();
        $stmt->close();

        /* 檢查資訊 */
        if (mysqli_num_rows($result) < 1) return AUTH_FORGETPASS_EMAIL_FAIL;

        /* 寄送確認 */
        $code = Generate_Code();

        /* 插入數據 */
        $stmt = $this->sqlcon->prepare("INSERT INTO {$this->sqlsetting_Forgetpass['table']} ({$this->sqlsetting_Forgetpass['UUID']}, {$this->sqlsetting_Forgetpass['Code']}, {$this->sqlsetting_Forgetpass['Last_time']}) VALUES (?, ?, UNIX_TIMESTAMP())");
        $stmt->bind_param("ss", $userdata[$this->sqlsetting_User['UUID_col']], $code);
        if (!$stmt->execute()) {
            $stmt = $this->sqlcon->prepare("UPDATE {$this->sqlsetting_Forgetpass['table']} SET {$this->sqlsetting_Forgetpass['Code']} = ?, {$this->sqlsetting_Forgetpass['Last_time']} = UNIX_TIMESTAMP() WHERE {$this->sqlsetting_Forgetpass['UUID']} = ?");
            $stmt->bind_param("ss", $code, $userdata[$this->sqlsetting_User['UUID_col']]);
        }
        $stmt->close();

        $this->run_Hook('acc_ForgetPass', $userdata, $email, $code, $this->sqlcon); //執行Hook
        return AUTH_FORGETPASS_LASTSTEP; //Done
    }

    /**
     * 更改忘記密碼
     * @param string $pass 密碼
     * @param string $Cpass 確認密碼
     * @param bool $RSAon 是否使用了RSA加密
     */
    function ForgetPass_set(string $pass, string $Cpass, bool $RSAon = false): int {
        /* 進入判斷程序 */

        //不能留空
        if (empty($Cpass) || empty($pass)) return AUTH_FORGETPASS_EMPTY;

        /* 解密 */
        if ($RSAon) {
            $pi_key = openssl_pkey_get_private($_SESSION['pvKey']);
            $dePass = openssl_private_decrypt(base64_decode($pass), $pass, $pi_key);
            $deCpass = openssl_private_decrypt(base64_decode($Cpass), $Cpass, $pi_key);
            if (!($dePass && $deCpass)) return AUTH_FORGETPASS_PASS_NOT_MATCH;
        }

        /* 消毒 */
        $pass = filter_var(trim($pass), FILTER_SANITIZE_STRING);
        $Cpass = filter_var(trim($Cpass), FILTER_SANITIZE_STRING);

        //檢查密碼是否一樣
        if ($pass != $Cpass) return AUTH_FORGETPASS_PASS_NOT_MATCH;

        /* 先取得資料 */
        $stmt = $this->sqlcon->prepare("SELECT {$this->sqlsetting_User['Name_col']}, {$this->$sqlsetting_User['Email_col']} FROM {$this->sqlsetting_User['table']} WHERE {$this->sqlsetting_User['UUID_col']} = ?;");
        $stmt->bind_param('s', $_SESSION['Auth']['uuid']);
        if (!$stmt->execute()) return AUTH_SERVER_ERROR;

        /* 分析結果 */
        $result = $stmt->get_result();
        $userdata = $result->fetch_assoc();
        $stmt->close();

        /* 判斷密碼強度 */
        if (!preg_match("/(?=.*?[A-Z])(?=.*?[a-z])/", $Cpass) || strlen($Cpass) < 8 ||
            $userdata[$this->sqlsetting_User['Email_col']] == $Cpass || $userdata[$this->sqlsetting_User['Name_col']] == $Cpass) return AUTH_FORGETPASS_PASS_NOT_STRONG;

        /* 消毒/加密 */
        $Cpass = 'Gblacklist' . $Cpass;
        $Cpass = hash('sha512', md5($Cpass));

        /* 寫入資料 */
        $stmt = $this->sqlcon->prepare("UPDATE {$this->sqlsetting_User['table']} SET {$this->sqlsetting_User['Password_col']} = ? WHERE {$this->sqlsetting_User['UUID_col']} = ?;");
        $stmt->bind_param('ss', $Cpass, $_SESSION['Auth']['uuid']);
        if (!$stmt->execute()) return AUTH_SERVER_ERROR;

        /* 回饋成功 */
        unset($_SESSION['Auth']['uuid']);
        return AUTH_FORGETPASS_COMPLETE;
    }

    /**
     * 添加掛勾<br/>
     * **目前可使用掛勾:**
     * + acc_activated *帳戶啟動後執行*
     * + acc_Check_NewIP *檢查登入ip後執行*
     * + acc_ForgetPass *執行忘記密碼func後執行*
     * + acc_register *用戶註冊後執行*
     * + acc_changeSetting_data *修改用戶資料後執行*
     * @param string $Hook_name 掛勾名
     * @param string $func_name 執行的程式名
     */
    public function add_Hook(string $Hook_name, string $func_name) {
        $a = array();
        $a[] = $func_name;
        $this->Hook_func[$Hook_name] = $a;
    }

    /**
     * 執行掛鉤
     * @param string|null $Hook_name 掛勾名
     * @param mixed ...$parameter 參數
     */
    private function run_Hook(string $Hook_name, ...$parameter) {
        if (isset($this->Hook_func[$Hook_name])) {
            foreach ($this->Hook_func[$Hook_name] as $func_name) {
                call_user_func($func_name, ...$parameter);
            }
        }
    }

    /**
     *產生亂數
     * @param int $length 長度
     * @return string 亂數
     */
    private function Generate_Code(int $length = 32): string {
        $chars = 'qwertyuiopasdfghjklzxcvbnm1234567890QWERTYUIOPASDFGHJKLZXCVBNM';
        $code = '';

        for ($i = 0; $i < $length; $i++) {
            $code .= substr($chars, rand() % 62, 1);
        }

        return $code;
    }
}