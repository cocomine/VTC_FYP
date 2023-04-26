<?php
/**
 * Created by IntelliJ IDEA.
 * User: user
 * Date: 24/12/2018
 * Time: 上午 3:34
 */

/* 判斷結尾 */
if(false !== strpos($_SERVER['REQUEST_URI'], '.php')){
    $url = str_replace('.php', '', $_SERVER['REQUEST_URI']);
    header('Location:'.$url);
    exit();
}

header("Content-type: image/webp");
header("Cache-Control: no-cache");
$imgurl = rand(1,4) .".webp";
readfile("bg/".$imgurl);