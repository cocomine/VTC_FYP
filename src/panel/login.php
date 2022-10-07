<?php
/*
 * Copyright (c) 2020.
 * Create by cocomine
 */

/**
 * Created by IntelliJ IDEA.
 * User: user
 * Date: 11/12/2018
 * Time: 下午 5:32
 */

use cocopixelmc\Auth\MyAuth;
use GuzzleHttp\Exception\RequestException;

/* header */
const title = "Login.title";
require_once('./stable/header.php'); //head

$auth = new MyAuth(Cfg_Sql_Host, Cfg_Sql_dbName, Cfg_Sql_dbUser, Cfg_Sql_dbPass, Cfg_500_Error_File_Path, Cfg_Cookies_Path); //startup
$auth -> checkAuth(); //start auth

/* 登出 */
if(@$_GET['logout'] == '1'){
    ob_clean();
    $auth -> logout(); //logout
    header("Location: /panel/login");
    exit();
}

/* Google 登入 */
if(isset($_GET['login'])){
    if($_GET['login'] === 'google'){
        $gclient = load_google_client();

        if(isset($_GET['code']) && !empty($_GET['code'])){
            try{
                $token = $gclient->fetchAccessTokenWithAuthCode($_GET['code']);
                $gclient->setAccessToken($token);

                $oauth = new Google_Service_Oauth2($gclient);
                $profile = $oauth->userinfo->get();

                $auth -> add_Hook('acc_Check_NewIP', 'acc_NewIP_Hook');
                if(!$auth->google_login($profile->getEmail())){
                    header('Location: /panel/register?email='.urlencode($profile->getEmail()).'&name='.urlencode($profile->getName()));
                    exit();
                }
            }catch (RequestException $e){
                login_form(null, AUTH_GOOGLE_ERROR);
            }

        }else{
            if(isset($_GET['error'])){
                login_form(null, AUTH_GOOGLE_ERROR);
            }else{
                $auth_url = $gclient->createAuthUrl();
                header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
                exit();
            }
        }
    }else{
        login_form(); //未有指定時
    }
}

/* 登入 */
if($_SERVER['REQUEST_METHOD'] == 'POST'){
    if(!$auth -> islogin){
        @session_start();
        if(isset($_POST['TwoFA_Code']) && $_SESSION['2FA']['Doing_2FA']){
            $auth -> TwoFA_check($_POST['TwoFA_Code'] ?? "");
        }else{
            $auth -> add_Hook('acc_Check_NewIP', 'acc_NewIP_Hook');
            $auth -> login($_POST['email'] ?? "", $_POST['password'] ?? "");
        }
    }
}

/* 激活 */
if(isset($_GET['code']) && !isset($_GET['login'])){
    $auth->add_Hook('acc_activated', 'acc_activated_Hook');
    $auth -> activated($_GET['code'] ?? "");
}

/* 確認登入 */
if($auth -> islogin){
    ob_clean();
    header("Location: /panel");
    exit();
}

/* 正常訪問 */
if($_SERVER['REQUEST_METHOD'] != 'POST' && !isset($_GET['code']) && !isset($_GET['login'])){
    login_form();
    unset($_SESSION['2FA']);
}

/**
 * 登錄表
 *
 * @param string|null $email The user email
 * @param int|null $status login status
 */
function login_form(int $status = null, string $email = null) {
    switch ($status) {
        case AUTH_WRONG_PASS:
            $msg = '<div class="alert alert-danger" role="alert">
                        ' . showText("Login.WRONG_PASS") . '
                    </div>';
            $msg2 = 'focused';
            break;
        case AUTH_NOT_DONE:
            $msg = '<div class="alert alert-danger" role="alert">
                        ' . showText("Login.NOT_DOEN") . '
                    </div>';
            $msg2 = 'focused';
            break;
        case AUTH_REGISTER_COMPLETE:
            $msg = '<div class="alert alert-success" role="alert">
                        ' . showText("Login.REGISTER_COMPLETE") . '
                    </div>';
            $msg2 = '';
            break;
        case AUTH_REGISTER_CODE_WRONG:
            $msg = '<div class="alert  alert-danger" role="alert">
                        ' . showText("Login.REGISTER_CODE_WRONG") . '
                    </div>';
            $msg2 = '';
            break;
        case AUTH_BLOCK_10:
            $msg = '<div class="alert  alert-warning" role="alert">
                        ' . showText("Login.BLOCK_10.0") . '<br> 
                        ' . showText("Login.BLOCK_10.1") . '
                    </div>';
            $msg2 = 'focused';
            break;
        case AUTH_BLOCK_30:
            $msg = '<div class="alert  alert-warning" role="alert">
                        ' . showText("Login.BLOCK_30.0") . '<br> 
                        ' . showText("Login.BLOCK_30.1") . '
                    </div>';
            $msg2 = 'focused';
            break;
        case AUTH_GOOGLE_ERROR:
            $msg = '<div class="alert  alert-danger" role="alert">' . showText("Login.AUTH_GOOGLE_ERROR") . '</div>';
            $msg2 = 'focused';
            break;
        default:
            $msg = '';
            $msg2 = '';
            break;
    }

    //指引文字
    $Text = array(
        "Login" => showText("Login.Login"),
        "welcome" => showText("Login.welcome"),
        "email" => showText("Login.email"),
        "password" => showText("Login.password"),
        "Remember_ME" => showText("Login.Remember_ME"),
        "forgotpass" => showText("Login.forgotpass"),
        "go_register" => showText("Login.go_register"),
        "Login_with_google" => showText("Login.Login_with_google")
    );

    echo <<<LOGIN_FROM
<!-- login area start -->
<div class="login-area login-bg">
    <div class="container">
        <div class="login-box ptb--100">
            <form action="/panel/login" method="post">
                <div class="login-form-head">
                    <h4>{$Text['Login']}</h4>
                    <p>{$Text['welcome']}</p>
                </div>
                {$msg}
                <div class="login-form-body">
                    <div class="form-gp {$msg2}">
                        <label for="Email">{$Text['email']}</label>
                        <input type="email" autocomplete="username" required="required" name="email" value="{$email}" id="Email" autofocus inputmode="email">
                        <i class="ti-email"></i>
                    </div>
                    <div class="form-gp">
                        <label for="Password">{$Text['password']}</label>
                        <input type="password" autocomplete="current-password" required="required" id="Password">
                        <i class="ti-lock"></i>
                    </div>
                    <div class="row mb-4 rmber-area">
                         <div class="col-6">
                            <div class="form-check mr-sm-2">
                                    <input type="checkbox" class="form-check-input" id="Remember_ME" name="Remember_ME">
                                    <label class="form-check-label" for="Remember_ME">{$Text['Remember_ME']}</label>
                            </div>
                        </div>
                        <div class="col-6 text-end">
                            <a href="forgotpass">{$Text['forgotpass']}</a>
                        </div>
                    </div>
                    <div class="submit-btn-area">
                        <button id="form_submit" type="submit">{$Text['Login']}<i class="ti-arrow-right"></i></button>
                        <div class="login-other row mt-4 justify-content-md-center">
                            <div class="col-6">
                                <a class="google-login" href="?login=google">{$Text['Login_with_google']}</a>
                            </div>
                        </div>
                    </div>
                    <div class="form-footer text-center mt-5">
                        <p class="text-muted">{$Text['go_register']}</p>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- login area end -->
LOGIN_FROM;
}

echo <<<Foot
<!-- require js -->
<script src="/panel/assets/js/require.js"></script>
<script>
require.config({
        baseUrl : "/panel/assets/js",
        paths:{
            jquery: "https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min",
            bootstrap: "https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/js/bootstrap.bundle.min"
        },
        shim:{
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
                deps: ["jquery"]
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
    "scripts"])
</script>
<script src="/panel/assets/js/sw-register.min.js"></script>
</body>
</html>
Foot;

function load_google_client(){
    //require_once('vendor/autoload.php');

    $gclient = new Google_Client();
    $gclient -> setAuthConfig('function/secret/credentials.json');
    $gclient -> setAccessType('offline'); // offline access
    $gclient -> setIncludeGrantedScopes(true); // incremental auth
    $gclient -> addScope([Google_Service_Oauth2::USERINFO_EMAIL, Google_Service_Oauth2::USERINFO_PROFILE]);
    $gclient -> setRedirectUri('https://gblacklist.cocopixelmc.com/panel/login?login=google');

    return $gclient;
}
