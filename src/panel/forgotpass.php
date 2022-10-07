<?php
/*
 * Copyright (c) 2020.
 * Create by cocomine
 */

/**
 * Created by IntelliJ IDEA.
 * User: user
 * Date: 24/3/2019
 * Time: 下午 8:32
 */
use cocopixelmc\Auth\MyAuth;

/* header */
$title = showText("ForgotPass.title");
require_once('./stable/header.php'); //head

$auth = new MyAuth(Cfg_Sql_Host, Cfg_Sql_dbName, Cfg_Sql_dbUser, Cfg_Sql_dbPass, Cfg_500_Error_File_Path, Cfg_Cookies_Path); //startup
$auth->checkAuth(); //start auth
$OutputScript = null;

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    if(!$auth->islogin){
        if(isset($_SESSION['Doing_Reset'])){
            //error_log("Work");
            $auth->ForgetPass_set($_POST['password'] ?? "", $_POST['confirm_pass'] ?? "");//傳送新密碼
            OutputScript();
        }else{
            $auth->add_Hook('acc_ForgetPass', 'acc_ForgetPass_Hook');
            $auth->ForgetPass($_POST['Email'] ?? "");//傳送電郵
        }
    }else{
        ob_clean();
        header("Location: /panel");
        exit();
    }
}

/* 確認登入 */
if ($auth->islogin) {
    ob_clean();
    header("Location: /panel");
    exit();
}

/* 修改密碼 */
if(isset($_GET['code'])){
    $auth->ForgetPass("", $_GET['code']);
    OutputScript();
}

/* 正常訪問 */
if($_SERVER['REQUEST_METHOD'] != 'POST' && !isset($_GET['code'])){
    ForgetPass_from();
    unset($_SESSION['uuid']);
    unset($_SESSION['Doing_Reset']);
}

echo <<<Foot
<!-- jquery latest version -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script
<!-- bootstrap 5 js -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-OERcA2EqjJCMA+/3y+gxIOqMEjwtxJY7qPCqsdltbNJuaOe923+mo//f6V8Qbsw3" crossorigin="anonymous"></script>
<script src="/panel/assets/js/owl.carousel.min.js"></script>
<script src="/panel/assets/js/metisMenu.min.js"></script>
<script src="/panel/assets/js/jquery.slimscroll.min.js"></script>
<script src="/panel/assets/js/jquery.slicknav.min.js"></script>
{$OutputScript}
<!-- others plugins -->
<script src="/panel/assets/js/plugins.js"></script>
<script src="/panel/assets/js/scripts.js"></script>
<script src="/panel/assets/js/sw-register.min.js"></script>
</body>
</html>
Foot;

function OutputScript(){
    global $OutputScript;
    $OutputScript = "
    <link rel=\"stylesheet\" href=\"/panel/assets/css/myself/meter.min.css\">
    <script async src=\"/panel/assets/js/myself/password-strength-meter.min.js\"></script>
    <script async src=\"https://cdnjs.cloudflare.com/ajax/libs/zxcvbn/4.4.2/zxcvbn.js\"></script>
    <script async src=\"/panel/assets/js/jsencrypt.min.js\"></script>
    <script async src=\"/panel/assets/js/myself/pass-encrypt.min.js\"></script>
    ";
}