<?php
/*
 * Copyright (c) 2020.
 * Create by cocomine
 */
use cocopixelmc\Auth\MyAuth;

/* header */
$title = showText("Block_login.title");
require_once('./stable/header.php'); //head

$auth = new MyAuth(Cfg_Sql_Host, Cfg_Sql_dbName, Cfg_Sql_dbUser, Cfg_Sql_dbPass, Cfg_500_Error_File_Path, Cfg_Cookies_Path); //startup

/* 移除登入 */
if($auth->Block_login(base64_decode($_GET['code'] ?? ""))){

    $Text = array(
        'status' => showText("Block_login.status"),
        'detailed' => showText("Block_login.detailed"),
        'go_login' => showText("Block_login.go_login")
    );
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
<!-- jquery latest version -->
<script
  src="https://code.jquery.com/jquery-3.4.0.min.js"
  integrity="sha256-BJeo0qm959uMBGb65z40ejJYGSgR7REI4+CW1fNKwOg="
  crossorigin="anonymous"></script>
<!-- bootstrap 4 js -->
<script src="//{$_SERVER['HTTP_HOST']}/panel/assets/js/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
<script src="//{$_SERVER['HTTP_HOST']}/panel/assets/js/owl.carousel.min.js"></script>
<script src="//{$_SERVER['HTTP_HOST']}/panel/assets/js/metisMenu.min.js"></script>
<script src="//{$_SERVER['HTTP_HOST']}/panel/assets/js/jquery.slimscroll.min.js"></script>
<script src="//{$_SERVER['HTTP_HOST']}/panel/assets/js/jquery.slicknav.min.js"></script>
<!-- others plugins -->
<script src="//{$_SERVER['HTTP_HOST']}/panel/assets/js/plugins.min.js"></script>
<script src="//{$_SERVER['HTTP_HOST']}/panel/assets/js/scripts.min.js"></script>
<script src="//{$_SERVER['HTTP_HOST']}/panel/assets/js/sw-register.min.js"></script>
</body>
</html>
Foot;
