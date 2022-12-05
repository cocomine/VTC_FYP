<?php
/*
 * Copyright (c) 2020.
 * Create by cocomine
 */

use panel\apis\notify;
use GeoIp2\Database\Reader;
use GeoIp2\Exception\AddressNotFoundException;
use MaxMind\Db\Reader\InvalidDatabaseException;

//自動載入器
spl_autoload_register(function ($class) {
    include_once '../' . str_replace('\\', '/', $class) . '.php';
});

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

const MAIL_RESET = 100;
const MAIL_ACTIVATE = 101;
const MAIL_WONG_NEWIP = 102;

/**
 *電郵隊列
 * @param string $To 收件者電郵
 * @param string $html 內容
 * @param int $type 類型
 * @param mysqli $sqlcon sql連結
 * @return bool 是否已經放入
 */
function SendMail(string $To, string $html, int $type, mysqli $sqlcon): bool {

    switch ($type) {
        case MAIL_RESET:
            $subject = showText('Email.forgetPass.subject');
            $From = 'auth@cocopixelmc.com;'.Cfg_site_title;
            $Reply_To = 'support@cocopixelmc.com;'.Cfg_site_title;
            break;
        case MAIL_ACTIVATE:
            $subject = showText('Email.activated.subject');
            $From = 'auth@cocopixelmc.com;'.Cfg_site_title;
            $Reply_To = 'support@cocopixelmc.com;'.Cfg_site_title;
            break;
        case MAIL_WONG_NEWIP:
            $subject = showText('Email.Wong-newIP.subject');
            $From = 'auth@cocopixelmc.com;'.Cfg_site_title;
            $Reply_To = 'support@cocopixelmc.com;'.Cfg_site_title;
            break;
        default:
            return false;
    }

    /* 放入隊列 */
    $stmt = $sqlcon->prepare("INSERT INTO Mail_queue (Send_To, Send_From, Subject, Reply_To, Body) VALUES (?, ?, ?, ?, ?)");
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
    if ($ip == null) $ip = $_SERVER['REMOTE_ADDR'];
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
    if ($ip == null) $ip = $_SERVER['REMOTE_ADDR'];
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
 */
function getBrowser(string $user_agent = null) {
    return get_browser($user_agent, true);
}

/**
 * 帳戶啟動後執行掛勾function
 * @param mysqli $sqlcon 數據庫連接[option]
 * @param array $code 用戶code
 */
function acc_activated_Hook(array $code, mysqli $sqlcon) {
    $notify = new notify($sqlcon);
    $notify->Send_notify($code[0], "fa-regular fa-thumbs-up", notify::$Status_Success, '/panel', '你的帳號已成功啟動!ヾ(≧▽≦*)o<br>Your account has been activated successfully!ヾ(≧▽≦*)o');
}

/**
 * 檢查ip後執行掛勾function
 * @param bool $isNewIP 是否新ip
 * @param array|null $userdata 用戶資料
 * @param string|null $code 驗證代碼
 * @param mysqli|null $sqlcon 數據庫連接[option]
 */
function acc_NewIP_Hook(bool $isNewIP, array $userdata = null, string $code = null, mysqli $sqlcon = null) {
    if ($isNewIP) {
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

        $bc = getBrowser();

        /* 訊息覆蓋 */
        $count = 2;
        $html = str_replace('%localCode%', $userdata['Language'], $html);
        $html = str_replace('%URL%', "https://".$_SERVER['SERVER_NAME']."/panel/Block_login?code=" . urlencode(base64_encode($code)), $html, $count);
        $html = str_replace('%NAME%', $userdata['Name'], $html);
        $html = str_replace('%IP%', filter_var($_SERVER['REMOTE_ADDR'], FILTER_SANITIZE_STRING), $html);
        $html = str_replace('%COUNTRY%', getCity(), $html);
        $html = str_replace('%BROWSER%', $bc['browser'] . $bc['version'] . ', ' . $bc['platform'], $html);
        $html = str_replace('%ISP%', getISP(), $html);

        /* 發出電郵 */
        $mail = [$userdata['Email'], $html, MAIL_WONG_NEWIP];
        SendMail($mail[0], $mail[1], $mail[2], $sqlcon);
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
    $html = str_replace('%URL%', "https://".$_SERVER['SERVER_NAME']."/panel/forgotpass?code=" . urlencode(base64_encode($userdata['UUID'] . "@" . $code)), $html, $count);
    $html = str_replace('%NAME%', $userdata['Name'], $html);
    SendMail($email, $html, MAIL_RESET, $sqlcon);
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
    $html = str_replace('%URL%', "https://".$_SERVER['SERVER_NAME']."/panel/login?code=" . urlencode(base64_encode($uuid . "@" . $ActivatedCode)), $html, $count);
    $html = str_replace('%NAME%', $name, $html);

    SendMail($email, $html, MAIL_ACTIVATE, $sqlcon);
}