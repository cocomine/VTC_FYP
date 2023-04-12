<?php
/*
 * Copyright (c) 2020.
 * Create by cocomine
 */

use cocomine\MyAuth;
use cocomine\MyAuthException;

/* header */
const title = "Block_login.title";
require_once('./stable/header.php'); //head

$auth = new MyAuth(Cfg_Sql_Host, Cfg_Sql_dbName, Cfg_Sql_dbUser, Cfg_Sql_dbPass, Cfg_Cookies_Path); //startup

/* 移除登入 */
try {
    if ($auth->Block_login(base64_decode($_GET['code'] ?? ""))) {
        successful();
    } else {
        /* 回首頁 */
        header("Location: /panel");
    }
} catch (MyAuthException $e) {
}

/* 輸出成功訊息 */
function successful(){
    $Text = showText("Block_login");

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
            "plugins":{
                deps: ["jquery"]
            },
            "scripts":{
                deps: ["jquery"]
            }
        }
});
require([
    "jquery", 
    "bootstrap", 
    "plugins",
    "scripts"], () => {
        $('#preloader').fadeOut('slow', function() { $(this).remove(); });
        $(document).trigger('load');
    })
</script>
<script src="/panel/assets/js/sw-register.js"></script>
</body>
</html>
Foot;
