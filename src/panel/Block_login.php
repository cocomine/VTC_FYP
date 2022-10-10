<?php
/*
 * Copyright (c) 2020.
 * Create by cocomine
 */
use cocopixelmc\Auth\MyAuth;

/* header */
const title = "Block_login.title";
require_once('./stable/header.php'); //head

$auth = new MyAuth(Cfg_Sql_Host, Cfg_Sql_dbName, Cfg_Sql_dbUser, Cfg_Sql_dbPass, Cfg_500_Error_File_Path, Cfg_Cookies_Path); //startup

/* 移除登入 */
if($auth->Block_login(base64_decode($_GET['code'] ?? ""))){

    $Text = showText("Block_login");
    /* 輸出成功訊息 */
    echo <<<TwoFA_FORM
<!-- login area start -->
<div class="login-area login-bg">
    <div class="container">
        <div class="login-box ptb--100">
            <form style="border-radius: 20px">
                <div class="login-form-head" style="border-radius: 20px">
                    <h4>{$Text['status']}</h4>
                    <p>{$Text['detailed']}</p>
                    <br>
                    <a class="btn btn-rounded btn-outline-light" href="/panel" role="button">{$Text['go_login']} &nbsp;&nbsp;<i class="ti-arrow-right"></i></a>
                </div>
            </form>
        </div>
    </div>
</div>
TwoFA_FORM;
}else{
    /* 回首頁 */
    header("Location: /panel");
}

/* Foot */
echo <<<Foot
<!-- require js -->
<script src="/panel/assets/js/require.js"></script>
<script>
require.config({
        baseUrl : "/panel/assets/js",
        paths:{
            jquery: "https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min",
            bootstrap: "https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/js/bootstrap.bundle.min",
            forge: "https://cdn.jsdelivr.net/npm/node-forge@1.3.1/dist/forge.min"
        },
        shim:{
            "owl.carousel.min":{
                deps:["jquery"]
            },
            "jquery.slimscroll.min":{
                deps:["jquery"]
            },
            "jquery.slicknav.min":{
                deps: ["jquery"]
            },
            "plugins":{
                deps: ["jquery"]
            },
            "scripts":{
                deps: ["jquery", "jquery.slicknav.min", "jquery.slimscroll.min", "owl.carousel.min"]
            }
        }
});
require([
    "jquery", 
    "bootstrap", 
    "owl.carousel.min",
    "metisMenu.min",
    "jquery.slimscroll.min",
    "jquery.slicknav.min",
    "plugins",
    "scripts"], () => {
        $(window).on('load', function() {
            $('#preloader').fadeOut('slow', function() { $(this).remove(); });
        });
    })
</script>
<script src="/panel/assets/js/sw-register.min.js"></script>
</body>
</html>
Foot;
