<?php
/*
 * Copyright (c) 2020.
 * Create by cocomine
 */

/* Cloudflare 環境下ip支援 */
if (isset($_SERVER["HTTP_CF_CONNECTING_IP"]) || $_SERVER['REMOTE_ADDR'] = '45.86.168.203') {
    $_SERVER['REMOTE_ADDR'] = filter_var($_SERVER["HTTP_CF_CONNECTING_IP"], FILTER_SANITIZE_STRING);
}

/* 判斷結尾 */
if (strpos($_SERVER['REQUEST_URI'], '.php') !== false) {
    $url = str_replace('.php', '', $_SERVER['REQUEST_URI']);
    header('Location:' . $url);
    exit();
}

/* header */
ob_start();
session_start();
require_once('./../vendor/autoload.php');
require_once('./function/Functions.php');
require_once('./../../secret/config.inc.php');
require_once('./Lang/Lang.php');
# require_once('../cocomine/MyAuth.php'); //is auto load
# require_once ('../cocomine/IPage.php');

header('Content-Type:text/html; charset=utf-8');

/* IP Block */
//IP_Block();

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
        <meta http-equiv="Content-Security-Policy"
              content="default-src 'self';
              script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdnjs.cloudflare.com https://unpkg.com https://cdn.jsdelivr.net https://*.googleapis.com https://*.google.com https://www.gstatic.com https://cdn.datatables.net https://api.mapbox.com https://cdn.amcharts.com;
              style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com https://unpkg.com https://cdn.jsdelivr.net https://*.googleapis.com https://*.google.com https://cdn.datatables.net https://api.mapbox.com;
              connect-src 'self' https://*.google.com https://*.lottiefiles.com https://*.tiles.mapbox.com https://api.mapbox.com https://events.mapbox.com https://cdn.datatables.net;
              img-src 'self' data: blob: https://www.gravatar.com;
              font-src 'self' https://fonts.gstatic.com;
              frame-src 'self' https://*.google.com;
              worker-src 'self' blob:;
              child-src blob:;" />
        <link rel="manifest" href="/panel/assets/manifest.json"/>
        <link rel="shortcut icon" type="image/png" href="/panel/assets/images/icon/favicon.png">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
        <link rel="stylesheet" href="/panel/assets/css/FA6/css/fontawesome.min.css">
        <link rel="stylesheet" href="/panel/assets/css/FA6/css/regular.min.css">
        <link rel="stylesheet" href="/panel/assets/css/FA6/css/brands.min.css">
        <link rel="stylesheet" href="/panel/assets/css/FA6/css/solid.min.css">
        <link rel="stylesheet" href="/panel/assets/css/themify-icons.min.css">
        <link rel="stylesheet" href="/panel/assets/css/metisMenu.min.css">
        <link rel="stylesheet" href="/panel/assets/css/scrollbar.css">
        <link rel="stylesheet" href="/panel/assets/css/owl.carousel.min.css">
        <link rel="stylesheet" href="/panel/assets/css/owl.theme.default.css">
        <link rel="stylesheet" href="/panel/assets/css/slicknav.min.css">
        <!-- others css -->
        <link rel="stylesheet" href="/panel/assets/css/typography.css">
        <link rel="stylesheet" href="/panel/assets/css/default-css.css">
        <link rel="stylesheet" href="/panel/assets/css/styles.css">
        <link rel="stylesheet" href="/panel/assets/css/responsive.css">
        <link rel="stylesheet" href="/panel/assets/css/myself/LoadingBar.min.css"/>
        <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css"/>
        <!-- modernizr css -->
        <script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>
        <script async src="/panel/assets/js/vendor/modernizr-custom.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/pace-js@latest/pace.min.js"></script>
        <script src="https://accounts.google.com/gsi/client" async defer></script>
    </head>
<body>
<!-- head start -->
    <!--[if lt IE 8]>
    <p class="browserupgrade"><?php echo showText("header.browserupgrade"); ?></p>
    <![endif]-->
    <noscript><p id="noscript"><?php echo showText("header.noscript"); ?></noscript>
    <!-- preloader area start -->
    <div id="preloader">
        <!--<div class="loader"><div></div><div></div><div></div><div></div></div>-->
        <div class="position-absolute top-50 start-50 translate-middle">
            <lottie-player src="https://assets7.lottiefiles.com/packages/lf20_j3ndxy3v.json" background="transparent" speed="1" style="width: 200px; height: 200px;" loop autoplay></lottie-player>
        </div>
    </div>
<!-- preloader area end -->
<!--google one tap -->
<?php
if(empty($_COOKIE['_ID'])){
    echo '<div id="g_id_onload"
     data-client_id="415107965516-cv5638cgsp5hcau4i5ts1ub9otktu3sp.apps.googleusercontent.com"
     data-login_uri="https://fyp.cocomine.cc/panel/login"
     data-login="google">
     </div>';
}

function IP_Block($dbServer = Cfg_Sql_Host, $dbName = Cfg_Sql_dbName, $dbUser = Cfg_Sql_dbUser, $dbPass = Cfg_Sql_dbPass) {
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
    $stmt = $sqlcon->prepare("SELECT IF(IP = ?, TRUE, FALSE) AS IP FROM Forever_Block_IP WHERE IP = ?");
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

    if ($data['IP']) {
        ob_clean();
        header('HTTP/1.1 403 Forbidden');
        require_once('./stable/403.php');
        exit();
    }
}