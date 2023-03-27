<?php
/*
 * Copyright (c) 2020.
 * Create by cocomine
 */

use apis\notify;
use GeoIp2\Database\Reader;
use GeoIp2\Exception\AddressNotFoundException;
use MaxMind\Db\Reader\InvalidDatabaseException;

//自動載入器
spl_autoload_register(function ($class) {
    include_once './' . str_replace('\\', '/', $class) . '.php';
});

/**
 * 輸出錯誤回應
 * @param int $code 錯誤代碼<br>
 *  400 = 請求錯誤<br>
 *  401 = 需要授權<br>
 *  403 = 拒絕訪問<br>
 *  404 = 路徑錯誤<br>
 *  405 = 不接受請求方式<br>
 *  500 = 伺服器錯誤
 * @link https://github.com/cocomine/VTC_FYP/blob/master/src/cocomine/doc/php/Functions.php.md#echo_errorint-code
 */
function echo_error(int $code) {
    if ($code === 403) {
        //沒有權限
        header("content-type: text/json; charset=utf-8");
        http_response_code(403);
        echo json_encode(array('code' => 403, 'Message' => showText("Error_Page.Dont_Come")));
    }
    if ($code === 401) {
        //需要登入
        header("content-type: text/json; charset=utf-8");
        http_response_code(401);
        echo json_encode(array('code' => 401, 'path' => '/panel/login'));
    }
    if ($code === 500) {
        //Server Error
        header("content-type: text/json; charset=utf-8");
        http_response_code(500);
        echo json_encode(array('code' => 500, 'Message' => showText("Error_Page.something_happened")));
    }
    if ($code === 404) {
        //Not Found
        header("content-type: text/json; charset=utf-8");
        http_response_code(404);
        echo json_encode(array('code' => 404, 'Message' => showText("Error_Page.Where_you_go")));
    }
    if ($code === 405) {
        /* 不符合任何請求 */
        header("content-type: text/json; charset=utf-8");
        http_response_code(405);
        echo json_encode(array('code' => 405, 'Message' => showText('Error_Page.405')));
    }
    if ($code === 400) {
        /* 錯誤請求 */
        header("content-type: text/json; charset=utf-8");
        http_response_code(400);
        echo json_encode(array('code' => 400, 'Message' => showText('Error_Page.400')));
    }
}

/**
 * 過濾陣列字串
 * @param array $array 陣列
 * @return array 過濾後陣列
 * @link https://github.com/cocomine/VTC_FYP/blob/master/src/cocomine/doc/php/Functions.php.md#array_sanitizearray-array
 */
function array_sanitize(array $array): array{
    return filter_var_array($array, FILTER_SANITIZE_STRING);
}

/**
 * 檢查電郵格式
 * @param string $email 電郵地址
 * @return bool 是否正確
 * @link https://github.com/cocomine/VTC_FYP/blob/master/src/cocomine/doc/php/Functions.php.md#verifyemailstring-email
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
 * @deprecated
 * @link https://github.com/cocomine/VTC_FYP/blob/master/src/cocomine/doc/php/Functions.php.md#encodeheaderstring-str
 */
function EncodeHeader(string $str): string {
    mb_internal_encoding('utf-8');
    return mb_encode_mimeheader($str);
}

/**
 * 產生亂數
 * @param int $length 長度
 * @return string 亂數
 * @link https://github.com/cocomine/VTC_FYP/blob/master/src/cocomine/doc/php/Functions.php.md#generate_codeint-length
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
 * 發送電郵,電郵隊列
 * @param string $To 收件者電郵
 * @param string $html 內容
 * @param int $type 類型, 請輸入0
 * @param mysqli $sqlcon sql連結
 * @param string|null $subject 主旨
 * @return bool 是否已經放入隊列
 * @link https://github.com/cocomine/VTC_FYP/blob/master/src/cocomine/doc/php/Functions.php.md#sendmailstring-to-string-html-int-type-mysqli-sqlcon-string-subject
 */
function SendMail(string $To, string $html, int $type, mysqli $sqlcon, string $subject = null): bool {

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
            $From = 'note@cocopixelmc.com;'.Cfg_site_title;
            $Reply_To = 'support@cocopixelmc.com;'.Cfg_site_title;
            break;
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
 * @link https://github.com/cocomine/VTC_FYP/blob/master/src/cocomine/doc/php/Functions.php.md#getcitystring-ip
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
 * @link https://github.com/cocomine/VTC_FYP/blob/master/src/cocomine/doc/php/Functions.php.md#getispstring-ip
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
 * @param string|null $user_agent 用戶資料, 由http header取得
 * @return array|false|object 回傳Browscap資料
 * @link https://github.com/cocomine/VTC_FYP/blob/master/src/cocomine/doc/php/Functions.php.md#getbrowserstring-user_agent
 */
function getBrowser(string $user_agent = null) {
    return get_browser($user_agent, true);
}