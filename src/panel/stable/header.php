<?php
/*
 * Copyright (c) 2020.
 * Create by cocomine
 */

ob_start();
require_once('./vendor/autoload.php');
require_once('./function/config.inc.php');
require_once('./Lang/Lang.php');
require_once('./function/MyAuth.php');
require_once('./function/Functions.php');

/* header */
header('Content-Type:text/html; charset=utf-8');

/* read API */

/* 判斷結尾 */
if(strpos($_SERVER['REQUEST_URI'], '.php') !== false){
    $url = str_replace('.php', '', $_SERVER['REQUEST_URI']);
    header('Location:'.$url);
    exit();
}

/* IP Block */
IP_Block();
session_start();

?>
    <!DOCTYPE html>
    <html class="no-js" lang="<?php echo $localCode ?? 'en'; ?>">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="x-ua-compatible" content="ie=edge">
        <title><?php echo showText(title); ?></title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#ff7112"/>
        <meta name="robots" content="noindex">
        <link rel="manifest" href="/panel/assets/manifest.json" />
        <link rel="shortcut icon" type="image/png" href="/panel/assets/images/icon/favicon.png">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-Zenh87qX5JnK2Jl0vWa8Ck2rdkQ2Bzep5IDxbcnCeuOxjzrPF/et3URy9Bv1WTRi" crossorigin="anonymous">
        <link rel="stylesheet" href="/panel/assets/css/FA6/css/fontawesome.min.css">
        <link rel="stylesheet" href="/panel/assets/css/FA6/css/regular.min.css">
        <link rel="stylesheet" href="/panel/assets/css/FA6/css/brands.min.css">
        <link rel="stylesheet" href="/panel/assets/css/FA6/css/solid.min.css">
        <link rel="stylesheet" href="/panel/assets/css/themify-icons.min.css">
        <link rel="stylesheet" href="/panel/assets/css/metisMenu.min.css">
        <link rel="stylesheet" href="/panel/assets/css/owl.carousel.min.css">
        <link rel="stylesheet" href="/panel/assets/css/slicknav.min.css">
        <!-- others css -->
        <link rel="stylesheet" href="/panel/assets/css/typography.css">
        <link rel="stylesheet" href="/panel/assets/css/default-css.css">
        <link rel="stylesheet" href="/panel/assets/css/styles.css">
        <link rel="stylesheet" href="/panel/assets/css/responsive.css">
        <link rel="stylesheet" href="/panel/assets/css/myself/LoadingBar.min.css" />
        <link rel="stylesheet" href="/panel/assets/css/myself/LoadingCard.min.css" />
        <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />
        <!-- modernizr css -->
        <script async src="/panel/assets/js/vendor/modernizr-custom.js"></script>
        <script async src='https://www.google.com/recaptcha/api.js'></script>
        <script src="https://cdn.jsdelivr.net/npm/pace-js@latest/pace.min.js"></script>
    </head>
    <body>
    <!-- head start -->
    <!--[if lt IE 8]>
    <p class="browserupgrade"><?php echo showText("header.browserupgrade");?></p>
    <![endif]-->
    <noscript><p id="noscript"><?php echo showText("header.noscript");?></noscript>
    <!-- preloader area start -->
    <div id="preloader">
        <div class="loader"><div></div><div></div><div></div><div></div></div>
    </div>
    <!-- preloader area end -->
    <!-- head end -->

<?php
function IP_Block($dbServer = Cfg_Sql_Host, $dbName = Cfg_Sql_dbName, $dbUser = Cfg_Sql_dbUser, $dbPass = Cfg_Sql_dbPass){
    /* Cloudflare 環境下ip支援 */
    if(isset($_SERVER["HTTP_CF_CONNECTING_IP"]) || $_SERVER['REMOTE_ADDR'] = '45.86.168.203'){
        $_SERVER['REMOTE_ADDR'] = filter_var($_SERVER["HTTP_CF_CONNECTING_IP"], FILTER_SANITIZE_STRING);
    }
    $ip = $_SERVER['REMOTE_ADDR'];

    /* 連結sql */
    $sqlcon = new mysqli($dbServer, $dbUser, $dbPass, $dbName);
    if ($sqlcon->connect_errno) {
        header('HTTP/1.1 500 Internal Server Error');
        require_once(Cfg_500_Error_File_Path);
        exit();
    }
    $sqlcon->query("SET NAMES utf8");

    /* 查詢 */
    $stmt = $sqlcon->prepare("SELECT IF(IP = ?, true, false) AS IP FROM Forever_Block_IP WHERE IP = ?");
    //echo $sqlcon->error;
    $stmt->bind_param("ss", $ip, $ip);
    if (!$stmt->execute()) {
        ob_clean();
        header('HTTP/1.1 500 Internal Server Error');
        require_once(Cfg_500_Error_File_Path);
        exit();
    }

    /* 分析結果 */
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    $stmt->close();
    $sqlcon->close();

    if($data['IP']) {
        ob_clean();
        header('HTTP/1.1 403 Forbidden');
        require_once('./stable/403.php');
        exit();
    }
}