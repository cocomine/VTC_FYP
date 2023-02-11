0<?php
/**
 * Copyright (c) 2020.
 * Create by cocomine
 */
$Http_header = filter_var($_SERVER['HTTP_ACCEPT_LANGUAGE'], FILTER_SANITIZE_STRING); //消毒

/* 取cookie語言 */
if(!empty($_COOKIE['Lang'])){
    $first_local[0] = filter_var($_COOKIE['Lang'], FILTER_SANITIZE_STRING);
}else{
    $first_local = explode(",", $Http_header); //取優先語言
}

$local = locale_accept_from_http($Http_header); //取其次語言

$LangJson = @file_get_contents($_SERVER['DOCUMENT_ROOT']."/panel/Lang/".$first_local[0].".json");//讀優先檔案
if($LangJson){
    $Lang = json_decode($LangJson, true);
    $localCode = $first_local[0];
}else{
    $LangJson = @file_get_contents($_SERVER['DOCUMENT_ROOT']."/panel/Lang/".$local.".json");//讀其次檔案
    if($LangJson){
        $Lang = json_decode($LangJson, true);
        $localCode = $local;
    }else{
        $Lang = json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT']."/panel/Lang/en.json"), true); //讀預設語言
        $localCode = 'en';
    }
}

$default = json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT']."/panel/Lang/en.json"), true); //讀預設語言

/**
 * 取得語言文字
 *
 * @param string $Path 路徑
 * @param string|null $localCode 語言代碼
 * @return string|array 輸出文字
 * @link https://github.com/cocomine/VTC_FYP/blob/master/src/cocomine/doc/php/multi_language.md
 */
function showText(string $Path, string $localCode = null) {
    global $Lang, $default;
    $PathStr = $Path;
    $Path = explode(".",$Path);
    $lang = $Lang;

    if($localCode !== null){
        $lang = @json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT']."/panel/Lang/".$localCode.".json"), true);
        if(empty($lang)){
            $lang = json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT']."/panel/Lang/en.json"), true);
        }
    }

    $Node = $lang['Translate'];
    $defaultNode = $default['Translate'];
    for($i = 0;$i<count($Path);$i++){
        $Node = $Node[$Path[$i]] ?? $defaultNode[$Path[$i]] ?? $PathStr;
        if($Node === $PathStr) break;
        $defaultNode = $defaultNode[$Path[$i]] ?? $PathStr;
    }
    return $Node;
}

/**
 * 設置使用語言
 *
 * @param string $localCode 語言代碼
 */
function setLang(string $localCode){
    global $Lang;

    $Lang = @json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT']."/panel/Lang/".$localCode.".json"), true);
    if(empty($Lang)){
        $Lang = json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT']."/panel/Lang/en.json"), true);
    }
}

/**
 * 取得語言代碼
 * @return string 語言代碼
 */
function getLocalCode(): string {
    global $localCode;
    return $localCode;
}