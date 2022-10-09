<?php
/**
 * Created by IntelliJ IDEA.
 * User: user
 * Date: 15/12/2018
 * Time: 下午 1:51
 */
require_once($_SERVER['DOCUMENT_ROOT'] . '/panel/Lang/Lang.php');
?>

<!doctype html>
<html class="no-js" lang="<?php echo $localCode ?? 'en'; ?>">

<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title><?php echo showText("Error_Page.500_title");?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" type="image/png" href="//<?php echo $_SERVER['HTTP_HOST'];?>/panel/assets/images/icon/favicon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-Zenh87qX5JnK2Jl0vWa8Ck2rdkQ2Bzep5IDxbcnCeuOxjzrPF/et3URy9Bv1WTRi" crossorigin="anonymous">    <!-- style css -->
    <link rel="stylesheet" href="//<?php echo $_SERVER['HTTP_HOST'];?>/panel/assets/css/default-css.css">
    <link rel="stylesheet" href="//<?php echo $_SERVER['HTTP_HOST'];?>/panel/assets/css/styles.css">
    <link rel="stylesheet" href="//<?php echo $_SERVER['HTTP_HOST'];?>/panel/assets/css/responsive.css">
    <!-- modernizr css -->
</head>

<body>
<!--[if lt IE 8]>
<p class="browserupgrade"><?php echo showText("header.browserupgrade");?></p>
<![endif]-->
<noscript><p id="noscript"><?php echo showText("header.noscript");?></noscript>
<!-- error area start -->
<div class="error-area ptb--100 text-center">
    <div class="container">
        <div class="error-content">
            <h2>500</h2>
            <img src="//<?php echo $_SERVER['HTTP_HOST'];?>/panel/assets/images/offline.svg">
            <p><?php echo showText("Error_Page.something_happened");?></p>
            <a href="//<?php echo $_SERVER['HTTP_HOST'];?>/panel"><?php echo showText("Error_Page.GoBack");?></a>
        </div>
    </div>
</div>
<!-- error area end -->
</html>