<!--?php require_once($_SERVER['DOCUMENT_ROOT'].'/panel/Lang/Lang.php'); ?-->
<!doctype html>
<html class="no-js" lang="<?php echo $localCode ?? 'en'; ?>">

<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title><?php echo showText("Error_Page.404_title");?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" type="image/png" href="//<?php echo $_SERVER['HTTP_HOST'];?>/panel/assets/images/icon/favicon.png">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <link rel="stylesheet" href="//<?php echo $_SERVER['HTTP_HOST'];?>/panel/assets/css/font-awesome.min.css">
    <link rel="stylesheet" href="//<?php echo $_SERVER['HTTP_HOST'];?>/panel/assets/css/themify-icons.min.css">
    <link rel="stylesheet" href="//<?php echo $_SERVER['HTTP_HOST'];?>/panel/assets/css/metisMenu.min.css">
    <link rel="stylesheet" href="//<?php echo $_SERVER['HTTP_HOST'];?>/panel/assets/css/owl.carousel.min.css">
    <!-- amcharts css -->
    <link rel="stylesheet" href="https://www.amcharts.com/lib/3/plugins/export/export.css" type="text/css" media="all" />
    <!-- style css -->
    <link rel="stylesheet" href="//<?php echo $_SERVER['HTTP_HOST'];?>/panel/assets/css/typography.min.css">
    <link rel="stylesheet" href="//<?php echo $_SERVER['HTTP_HOST'];?>/panel/assets/css/default-css.min.css">
    <link rel="stylesheet" href="//<?php echo $_SERVER['HTTP_HOST'];?>/panel/assets/css/styles.min.css">
    <link rel="stylesheet" href="//<?php echo $_SERVER['HTTP_HOST'];?>/panel/assets/css/responsive.min.css">
    <!-- modernizr css -->
    <script src="//<?php echo $_SERVER['HTTP_HOST'];?>/panel/assets/js/vendor/modernizr-2.8.3.min.js"></script>
</head>

<body>
    <!--[if lt IE 8]>
    <p class="browserupgrade"><?php echo showText("header.browserupgrade");?></p>
    <![endif]-->
    <noscript><p id="noscript"><?php echo showText("header.noscript");?></noscript>
    <!-- preloader area start -->
    <div id="preloader">
        <div class="loader"></div>
    </div>
    <!-- preloader area end -->
    <!-- error area start -->
    <div class="error-area ptb--100 text-center">
        <div class="container">
            <div class="error-content">
                <h2>404</h2>
                <img src="//<?php echo $_SERVER['HTTP_HOST'];?>/panel/assets/images/offline.svg">
                <p><?php echo showText("Error_Page.Where_you_go");?></p>
                <a href="//<?php echo $_SERVER['HTTP_HOST'];?>/panel"><?php echo showText("Error_Page.GoBack");?></a>
            </div>
        </div>
    </div>
    <!-- error area end -->

    <!-- jquery latest version -->
    <script
            src="https://code.jquery.com/jquery-3.4.0.min.js"
            integrity="sha256-BJeo0qm959uMBGb65z40ejJYGSgR7REI4+CW1fNKwOg="
            crossorigin="anonymous"></script>
    <!-- bootstrap 4 js -->
    <script src="//<?php echo $_SERVER['HTTP_HOST'];?>/panel/assets/js/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
    <script src="//<?php echo $_SERVER['HTTP_HOST'];?>/panel/assets/js/owl.carousel.min.js"></script>
    <script src="//<?php echo $_SERVER['HTTP_HOST'];?>/panel/assets/js/metisMenu.min.js"></script>
    <script src="//<?php echo $_SERVER['HTTP_HOST'];?>/panel/assets/js/jquery.slimscroll.min.js"></script>
    <script src="//<?php echo $_SERVER['HTTP_HOST'];?>/panel/assets/js/jquery.slicknav.min.js"></script>
    <!-- others plugins -->
    <script src="//<?php echo $_SERVER['HTTP_HOST'];?>/panel/assets/js/plugins.min.js"></script>
    <script src="//<?php echo $_SERVER['HTTP_HOST'];?>/panel/assets/js/scripts.min.js"></script>
</body>

</html>