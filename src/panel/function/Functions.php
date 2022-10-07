<?php
/*
 * Copyright (c) 2020.
 * Create by cocomine
 */

use GeoIp2\Database\Reader;
use GeoIp2\Exception\AddressNotFoundException;
use MaxMind\Db\Reader\InvalidDatabaseException;
use phpbrowscap\Browscap;

//自動載入器
spl_autoload_register(function ($Class) {
    include './function/' . str_replace("cocomine\API\\", "", $Class) . '.php';
});

function mystrip($value) {
    if (get_magic_quotes_gpc()) {
        $value = stripcslashes($value);
    }
    return $value;
}

/**
 * 檢查電郵格式
 * @param string $email 電郵地址
 * @return bool 是否正確
 */
function VerifyEmail(string $email): bool {
    $pattern = "/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/";
    if (preg_match($pattern, $email)) {
        return true;
    } else {
        return false;
    }
}

/**
 * utf-8轉換
 * @param string $str 字串
 * @return string 轉換後字串
 */
function EncodeHeader(string $str): string {
    mb_internal_encoding('utf-8');
    return mb_encode_mimeheader($str);
}

/**
 *產生亂數
 * @param int $length 長度
 * @return string 亂數
 */
function Generate_Code(int $length = 32): string {
    $chars = 'qwertyuiopasdfghjklzxcvbnm1234567890QWERTYUIOPASDFGHJKLZXCVBNM';
    $code = '';

    for ($i = 0; $i < $length; $i++) {
        $code .= substr($chars, rand() % 62, 1);
    }

    return $code;
}

/**
 *電郵隊列
 * @param string $To 收件者電郵
 * @param string $html 內容
 * @param int $type 類型
 * @param mysqli $sqlcon sql連結
 * @param array $sqlsetting_Mail_queue SQL tables Setting
 * @return bool 是否已經放入
 */
function SendMail(string $To, string $html, int $type, array $sqlsetting_Mail_queue, mysqli $sqlcon): bool {
    switch ($type) {
        case AUTH_MAIL_RESET:
            $subject = showText('Email.forgetPass.subject');
            $From = 'auth@cocopixelmc.com;Global blacklist';
            $Reply_To = 'support@cocopixelmc.com;Global blacklist Support';
            break;
        case AUTH_MAIL_ACTIVATE:
            $subject = showText('Email.activated.subject');
            $From = 'auth@cocopixelmc.com;Global blacklist';
            $Reply_To = 'support@cocopixelmc.com;Global blacklist Support';
            break;
        case AUTH_MAIL_WONG_NEWIP:
            $subject = showText('Email.Wong-newIP.subject');
            $From = 'auth@cocopixelmc.com;Global blacklist';
            $Reply_To = 'support@cocopixelmc.com;Global blacklist Support';
            break;
        default:
            return false;
            break;
    }

    /* 放入隊列 */
    $stmt = $sqlcon->prepare("INSERT INTO {$sqlsetting_Mail_queue['table']} ({$sqlsetting_Mail_queue['Send_To']}, {$sqlsetting_Mail_queue['Send_From']}, {$sqlsetting_Mail_queue['Subject']}, {$sqlsetting_Mail_queue['Reply_To']}, {$sqlsetting_Mail_queue['Body']}) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $To, $From, $subject, $Reply_To, $html);
    if ($stmt->execute()) {
        return true;
    }

    return false;
}

/**
 * 取得城市
 * @param string|null $ip IP地址
 * @return string 回傳城市資料
 */
function getCity(string $ip = null): string {
    if ($ip == null)
        $ip = $_SERVER['REMOTE_ADDR'];
    require './function/GeoIP2/geoip2.phar';
    $reader = new Reader('./function/GeoIP2/GeoLite2-City.mmdb');

    /* 分辨語言 */
    $LocalCode = getLocalCode();
    if (strpos($LocalCode, 'de') !== false) {
        $LocalCode = 'de';
    } elseif (strpos($LocalCode, 'es') !== false) {
        $LocalCode = 'es';
    } elseif (strpos($LocalCode, 'fr') !== false) {
        $LocalCode = 'fr';
    } elseif (strpos($LocalCode, 'ja') !== false) {
        $LocalCode = 'ja';
    } elseif (strpos($LocalCode, 'pt') !== false) {
        $LocalCode = 'pt-BR';
    } elseif (strpos($LocalCode, 'ru') !== false) {
        $LocalCode = 'ru';
    } elseif (strpos($LocalCode, 'zh') !== false) {
        $LocalCode = 'zh-CN';
    } else {
        $LocalCode = 'en';
    }

    /* 取得資料 */
    try {
        $record = $reader->city($ip);
        $city = $record->country->names[$LocalCode] . ', ' . $record->continent->names[$LocalCode];
    } catch (AddressNotFoundException|InvalidDatabaseException $e) {
        $city = showText('Email.Wong-newIP.Unknown_Location');
    }

    return $city;
}

/**
 * 取得ISP
 * @param string|null $ip IP地址
 * @return string 回傳ISP資料
 */
function getISP(string $ip = null): string {
    if ($ip == null)
        $ip = $_SERVER['REMOTE_ADDR'];
    require './function/GeoIP2/geoip2.phar';
    $reader = new Reader('./function/GeoIP2/GeoLite2-ASN.mmdb');

    /* 取得資料 */
    try {
        $record = $reader->asn($ip);
        $city = $record->autonomousSystemOrganization;
    } catch (AddressNotFoundException|InvalidDatabaseException $e) {
        $city = showText('Email.Wong-newIP.Unknown_Location');
    }

    return $city;
}

/**
 * 取得瀏覽器
 * @param string|null $user_agent
 * @return array|object 回傳Browscap資料
 * @throws \phpbrowscap\Exception
 */
function getBrowser(string $user_agent = null) {
    require_once './function/Browscap/Browscap.php';
    $bc = new Browscap('./function/Browscap');
    return $bc->getBrowser($user_agent);
}

/**
 * 雙重驗證表
 *
 * @param int|null $status login status
 */
function TwoFA_form(int $status = null) {
    switch ($status) {
        case AUTH_2FA_WRONG:
            $msg = '<div class="alert alert-danger" role="alert">
                        ' . showText("TwoFA.2FA_WRONG") . '
                    </div>';
            break;
        case AUTH_2FA_DUE:
            $msg = '<div class="alert alert-danger" role="alert">
                        ' . showText("TwoFA.2FA_DUE") . '
                    </div>';
            break;
        default:
            $msg = '';
            break;
    }

    $Text = array( //指引文字
        "2FA" => showText("TwoFA.2FA"),
        "welcome" => showText("TwoFA.welcome"),
        "2FACode" => showText("TwoFA.2FACode"),
        "Login" => showText("TwoFA.Login"),
        "ReDo" => showText("TwoFA.ReDo"),
        "Can't_use_phone" => showText("TwoFA.Can't_use_phone")
    );

    echo <<<TwoFA_FORM
<!-- login area start -->
<div class="login-area login-bg">
    <div class="container">
        <div class="login-box ptb--100">
            <form action="/panel/login" method="post" id="2FA-Form">
                <div class="login-form-head">
                    <h4>{$Text['2FA']}</h4>
                    <p>{$Text['welcome']}</p>
                </div>
                {$msg}
                <div class="login-form-body">
                    <div class="form-gp focused">
                        <label for="2FA">{$Text['2FACode']}</label>
                        <input  type='text' pattern='[0-9a-zA-Z]{6}' id='2FA_Code' name='TwoFA_Code' autocomplete='off' autofocus required maxlength='6'>
                        <i class="ti-key"></i>
                    </div>
                    <div class="submit-btn-area">
                        <button id="form_submit" type="submit">{$Text['Login']}<i class="ti-arrow-right"></i></button>
                    </div>
                    <div class="form-footer text-center mt-5">
                        <p class="text-muted">{$Text['ReDo']}</p>
                    </div>
                    <div class="form-footer mt-5">
                        <small class="text-muted">{$Text["Can't_use_phone"]}</small>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
TwoFA_FORM;
}

/**
 * 忘記密碼表
 * @param int|null $status ForgetPass status
 */
function ForgetPass_from(int $status = null) {
    switch ($status) {
        case AUTH_FORGETPASS_EMAIL_FAIL:
            $msg = '<div class="alert alert-danger" role="alert">
                        ' . showText("ForgotPass.EMAIL_FAIL") . '
                    </div>';
            break;
        case AUTH_FORGETPASS_LASTSTEP:
            $msg = '<div class="alert alert-primary" role="alert">
                        ' . showText("ForgotPass.LASTSTEP") . '
                    </div>';
            break;
        case AUTH_FORGETPASS_CODE_WRONG:
            $msg = '<div class="alert alert-danger" role="alert">
                        ' . showText("ForgotPass.CODE_WRONG") . '
                    </div>';
            break;
        case AUTH_FORGETPASS_COMPLETE:
            $msg = '<div class="alert alert-success" role="alert">
                        ' . showText("ForgotPass.COMPLETE") . '
                    </div>';
            break;
        case AUTH_FORGETPASS_YOUR_BOT:
            $msg = '<div class="alert alert-danger" role="alert">
                        ' . showText("ForgotPass.YOUR_BOT") . '
                    </div>';
            break;
        default:
            $msg = '';
            break;
    }

    $Text = array( //指引文字
        "ForgotPass" => showText("ForgotPass.ForgotPass"),
        "welcome" => showText("ForgotPass.welcome"),
        "Email" => showText("ForgotPass.Email"),
        "Email_Tips" => showText("ForgotPass.Email_Tips"),
        "FindPass" => showText("ForgotPass.FindPass"),
        "go_login" => showText("ForgotPass.go_login")
    );

    echo <<<FORGETPASS_FROM
<div class="login-area login-bg">
        <div class="container">
            <div class="login-box ptb--100">
                <form action="/panel/forgotpass" method="post">
                    <div class="login-form-head">
                        <h4>{$Text['ForgotPass']}</h4>
                        <p>{$Text['welcome']}</p>
                    </div>
                    {$msg}
                    <div class="login-form-body">
                        <div class="form-gp">
                            <label for="Email">{$Text['Email']}</label>
                            <input type="email" id="Email" autocomplete="username" required="required" name="Email" title="{$Text['Email_Tips']}" autofocus inputmode="email">
                            <i class="ti-email"></i>
                        </div>
                        <div id="g-recaptcha" class="g-recaptcha" data-sitekey="6Le90ykTAAAAAOgxgMUBE-hW7OivFZs1ebR5btuu" data-callback="recaptchacall"></div>
                        <div class="submit-btn-area mt-5">
                            <button id="form_submit" type="submit">{$Text['FindPass']} <i class="ti-arrow-right"></i></button>
                        </div>
                        <div class="form-footer text-center mt-5">
                            <p class="text-muted">{$Text['go_login']}</p>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
FORGETPASS_FROM;

}

/**
 * 忘記密碼修改密碼表
 * @param int|null $status 狀態
 */
function ForgetPassSet_from(int $status = null) {
    switch ($status) {
        case AUTH_FORGETPASS_EMPTY:
            $msg = '<div class="alert alert-danger" role="alert">
                        ' . showText("ForgotPass.EMPTY") . '
                    </div>';
            break;
        case AUTH_FORGETPASS_PASS_NOT_MATCH:
            $msg = '<div class="alert alert-danger" role="alert">
                        ' . showText("ForgotPass.PASS_NOT_MATCH") . '
                    </div>';
            break;
        case AUTH_FORGETPASS_PASS_NOT_STRONG:
            $msg = '<div class="alert alert-danger" role="alert">
                        ' . showText("ForgotPass.PASS_NOT_STRONG") . '
                    </div>';
            break;
        default:
            $msg = '';
            break;
    }

    $Text = array( //指引文字
        "ForgotPass" => showText("ForgotPass.ForgotPass"),
        "welcome2" => showText("ForgotPass.welcome2"),
        "NewPass" => showText("ForgotPass.NewPass"),
        "ConfirmNewPass" => showText("ForgotPass.ConfirmNewPass"),
        "Not_Match_Wrong" => showText("ForgotPass.Not_Match_Wrong"),
        "passStrength" => showText("ForgotPass.passStrength"),
        "condition0" => showText("ForgotPass.condition.0"),
        "condition1" => showText("ForgotPass.condition.1"),
        "condition2" => showText("ForgotPass.condition.2"),
        "condition3" => showText("ForgotPass.condition.3"),
        "condition4" => showText("ForgotPass.condition.4"),
        "Change_Pass" => showText("ForgotPass.Change_Pass")
    );

    echo <<<FORGETPASSSET_FROM
<div class="login-area login-bg">
        <div class="container">
            <div class="login-box ptb--100">
                <form action="/panel/forgotpass" method="post">
                    <div class="login-form-head">
                        <h4>{$Text['ForgotPass']}</h4>
                        <p>{$Text['welcome2']}</p>
                    </div>
                    {$msg}
                    <div class="login-form-body">
                        <div class="form-gp">
                            <label for="Password">{$Text['NewPass']}</label>
                            <input type="password" autocomplete="new-password" id="Password" required="required" autofocus>
                            <i class="ti-lock"></i>
                        </div>
                        <div class="form-gp">
                            <label for="Password2">{$Text['ConfirmNewPass']}</label>
                            <input type="password" autocomplete="new-password" required="required" id="Password2" data-placement="auto" data-toggle="popover" data-html="true" data-trigger="manual" data-content="<i class='ti-alert' style='color:red;'></i> {$Text['Not_Match_Wrong']}">
                            <i class="ti-lock"></i>
                        </div>
                        <div class="form-gp">
                            <p>
                                {$Text['passStrength']} <span id='password-strength-text'>--</span> <br> 
                                <meter max='5' value='1' id='password-strength-meter'></meter>
                            </p> 
                            <p>
                                <b>{$Text['condition0']}</b><br>
                                1. {$Text['condition1']}<br>
                                2. {$Text['condition2']}<br>
                                3. {$Text['condition3']}<br>
                                4. {$Text['condition4']}
                            </p>
                        </div>
                        <div class="submit-btn-area mt-5">
                            <button id="form_submit" type="submit">{$Text['Change_Pass']} <i class="ti-arrow-right"></i></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
FORGETPASSSET_FROM;

}

/**
 * 帳戶啟動後執行掛勾function
 * @param mysqli $sqlcon 數據庫連接[option]
 * @param string $uuid 用戶id
 */
function acc_activated_Hook(string $uuid, mysqli $sqlcon) {
    require_once('./function/notifyAPI.php');
    $notify = new notifyAPI($sqlcon);
    $notify->Send_notify($uuid, "fa fa-thumbs-up", notifyAPI::$Status_Success, '/panel', '歡迎!新用戶!ヾ(≧▽≦*)o<br>Welcome! New users!ヾ(≧▽≦*)o');
}

/**
 * 檢查ip後執行掛勾function
 * @param bool $isNewIP 是否新ip
 * @param array|null $userdata 用戶資料
 * @param string|null $code 驗證代碼
 * @param mysqli|null $sqlcon 數據庫連接[option]
 * @throws \phpbrowscap\Exception
 */
function acc_NewIP_Hook(bool $isNewIP, array $userdata = null, string $code = null, mysqli $sqlcon = null) {
    if ($isNewIP) {
        $sqlsetting_Mail_queue = array(
            'table' => 'Mail_queue',
            'ID' => 'ID',
            'Send_To' => 'Send_To',
            'Send_From' => 'Send_From',
            'Subject' => 'Subject',
            'Reply_To' => 'Reply_To',
            'Body' => 'Body',
            'Fail' => 'Fail',
            'Sending' => 'Sending',
            'Send_Time' => 'Send_Time',
            'Create_Time' => 'Create_Time'
        );

        /* 傳送電郵 */
        $html = file_get_contents('./stable/Wong-newIP-mail.html');

        /* 語言覆蓋 */
        $start = 0;
        while ((strpos($html, "<Lang", $start + 6) !== false)) {
            $start = strpos($html, "<Lang", $start + 6);
            $end = strpos($html, "/>", $start);
            $Path = substr($html, $start + 6, $end - $start - 7);
            $replace = substr($html, $start, $end - $start + 2);
            $html = str_replace($replace, showText($Path), $html);
        }

        /* 訊息覆蓋 */
        $count = 2;
        $html = str_replace('%localCode%', $userdata['Language'], $html);
        $html = str_replace('%URL%', "https://gblacklist.cocopixelmc.com/panel/Block_login?code=" . urlencode(base64_encode($code)), $html, $count);
        $html = str_replace('%NAME%', $userdata['Name'], $html);
        $html = str_replace('%IP%', filter_var($_SERVER['REMOTE_ADDR'], FILTER_SANITIZE_STRING), $html);
        $html = str_replace('%COUNTRY%', getCity(), $html);
        $html = str_replace('%BROWSER%', getBrowser()->Browser . ', ' . getBrowser()->Platform, $html);
        $html = str_replace('%ISP%', getISP(), $html);

        /* 發出電郵 */
        $mail = [$userdata['Email'], $html, AUTH_MAIL_WONG_NEWIP];
        SendMail($mail[0], $mail[1], $mail[2], $sqlsetting_Mail_queue, $sqlcon);
    }

}

/**
 * 忘記密碼email傳送掛勾function
 * @param array $userdata 用戶資料
 * @param string $email 用戶電郵地址
 * @param string $code 驗證代碼
 * @param mysqli $sqlcon 數據庫連接[option]
 */
function acc_ForgetPass_Hook(array $userdata, string $email, string $code, mysqli $sqlcon) {
    $sqlsetting_Mail_queue = array(
        'table' => 'Mail_queue',
        'ID' => 'ID',
        'Send_To' => 'Send_To',
        'Send_From' => 'Send_From',
        'Subject' => 'Subject',
        'Reply_To' => 'Reply_To',
        'Body' => 'Body',
        'Fail' => 'Fail',
        'Sending' => 'Sending',
        'Send_Time' => 'Send_Time',
        'Create_Time' => 'Create_Time'
    );

    /* 傳送電郵 */
    $html = file_get_contents('./stable/forgetpass-mail.html');

    /* 語言覆蓋 */
    $start = 0;
    while ((strpos($html, "<Lang", $start + 6) !== false)) {
        $start = strpos($html, "<Lang", $start + 6);
        $end = strpos($html, "/>", $start);
        $Path = substr($html, $start + 6, $end - $start - 7);
        $replace = substr($html, $start, $end - $start + 2);
        $html = str_replace($replace, showText($Path, $userdata['Language']), $html);
    }

    /* 訊息覆蓋 */
    $count = 2;
    $html = str_replace('%localCode%', $userdata['Language'], $html);
    $html = str_replace('%URL%', "https://gblacklist.cocopixelmc.com/panel/forgotpass?code=" . urlencode(base64_encode($userdata['UUID'] . "@" . $code)), $html, $count);
    $html = str_replace('%NAME%', $userdata['Name'], $html);
    SendMail($email, $html, AUTH_MAIL_RESET, $sqlsetting_Mail_queue, $sqlcon);
}

/**
 * 修改用戶資料後執行掛勾function
 * @param string $uuid 用戶id
 * @param string $email 用戶電郵地址
 * @param string $ActivatedCode 驗證代碼
 * @param string $name 用戶名稱
 * @param mysqli $sqlcon 數據庫連接[option]
 */
function acc_Activated_Mail_Hook(string $uuid, string $email, string $ActivatedCode, string $name, mysqli $sqlcon) {
    global $localCode;
    $sqlsetting_Mail_queue = array(
        'table' => 'Mail_queue',
        'ID' => 'ID',
        'Send_To' => 'Send_To',
        'Send_From' => 'Send_From',
        'Subject' => 'Subject',
        'Reply_To' => 'Reply_To',
        'Body' => 'Body',
        'Fail' => 'Fail',
        'Sending' => 'Sending',
        'Send_Time' => 'Send_Time',
        'Create_Time' => 'Create_Time'
    );

    /* 傳送電郵 */
    $html = file_get_contents('./stable/activated-mail.html');

    /* 語言覆蓋 */
    $start = 0;
    while ((strpos($html, "<Lang", $start + 6) !== false)) {
        $start = strpos($html, "<Lang", $start + 6);
        $end = strpos($html, "/>", $start);
        $Path = substr($html, $start + 6, $end - $start - 7);
        $replace = substr($html, $start, $end - $start + 2);
        $html = str_replace($replace, showText($Path), $html);
    }

    /* 訊息覆蓋 */
    $count = 2;
    $html = str_replace('%localCode%', $localCode ?? '', $html);
    $html = str_replace('%URL%', "https://gblacklist.cocopixelmc.com/panel/login?code=" . urlencode(base64_encode($uuid . "@" . $ActivatedCode)), $html, $count);
    $html = str_replace('%NAME%', $name, $html);

    SendMail($email, $html, AUTH_MAIL_ACTIVATE, $sqlsetting_Mail_queue, $sqlcon);
}