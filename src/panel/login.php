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

use cocomine\MyAuth;

/* header */
const title = "Login.title";
require_once('./stable/header.php'); //head

$auth = new MyAuth(Cfg_Sql_Host, Cfg_Sql_dbName, Cfg_Sql_dbUser, Cfg_Sql_dbPass, Cfg_500_Error_File_Path, Cfg_Cookies_Path); //startup
$auth->checkAuth(); //start auth

/* 登出 */
if (isset($_GET['logout'])) {
    ob_clean();
    $auth->logout(); //logout
    header("Location: /panel/login");
    exit();
}

/* 登入 */
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    /* post 請求 */
    /* Google one tap 登入 */
    if (isset($_POST['login']) && $_POST['login'] == 'google') {
        try {
            $gclient = load_google_client();
        } catch (\Google\Exception $e) {
            header("Location: /panel/login");
            exit();
        }

        if (!empty($_POST['credential'])) {
            try {
                $payload = $gclient->verifyIdToken($_POST['credential']);
                $auth->add_Hook('acc_Check_NewIP', 'acc_NewIP_Hook');
                if ($auth->google_login($payload['email'])) header("Location: /panel");
                else header('Location: /panel/register?email=' . $payload['email'] . '&name=' . $payload['name']);
            } catch (Exception $e) {
                header("Location: /panel/login");
            }
        } else {
            header("Location: /panel/login");
        }
        exit();
    }

    ob_clean();
    header("content-type: text/json; charset=UTF-8"); //is json
    $data = json_decode(file_get_contents("php://input"), true);

    //已經登入
    if ($auth->islogin) {
        ob_clean();
        echo json_encode(array(
            'code' => AUTH_OK,
            'Message' => ResultMsg(AUTH_OK)
        ));
        exit();
    }

    //無法解釋json
    if ($data == null) {
        http_response_code(500);
        echo json_encode(array(
            'code' => 500,
            'Message' => showText('Error')
        ));
        exit();
    }

    /*if (isset($_POST['TwoFA_Code']) && $_SESSION['2FA']['Doing_2FA']) {
        $auth->TwoFA_check($_POST['TwoFA_Code'] ?? "");
    }*/

    $auth->add_Hook('acc_Check_NewIP', 'acc_NewIP_Hook');
    $status = $auth->login($data['email'], $data['password'], $data['remember_me'] == 'on', $_SESSION['pvKey']);
    echo json_encode(array(
        'code' => $status,
        'Message' => ResultMsg($status),
    ));
    exit();
} else {
    /* get 請求 */
    /* 已經登入 */
    if ($auth->islogin) {
        ob_clean();
        header("Location: /panel");
        exit();
    }

    /* 正常訪問 */
    if (empty($_GET['code']) && empty($_GET['login'])) {
        login_form();
        //unset($_SESSION['2FA']);
    }

    /* Google 登入 */
    if (isset($_GET['login']) && $_GET['login'] === 'google') {
        try {
            $gclient = load_google_client();
        } catch (\Google\Exception $e) {
            login_form(true);
        }

        if (!empty($_GET['code'])) {
            try {
                $token = $gclient->fetchAccessTokenWithAuthCode($_GET['code']);
                $gclient->setAccessToken($token);

                $oauth = new Google_Service_Oauth2($gclient);
                $profile = $oauth->userinfo->get();

                $auth->add_Hook('acc_Check_NewIP', 'acc_NewIP_Hook');
                if ($auth->google_login($profile->getEmail())) header("Location: /panel");
                else {
                    header('Location: /panel/register?email=' . $profile->getEmail() . '&name=' . $profile->getName());
                    exit();
                }
            } catch (Exception $e) {
                login_form(true);
            }
        } else {
            if (isset($_GET['error'])) {
                login_form(true);
            } else {
                $auth_url = $gclient->createAuthUrl();
                header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
                exit();
            }
        }
    }

    /* 激活 */
    if (isset($_GET['code']) && !isset($_GET['login'])) {
        $auth->add_Hook("acc_activated", "acc_activated_Hook");
        $status = $auth->activated($_GET['code'] ?? "");
        login_form(false, $status);
    }
}

/**
 * 登錄表
 *
 * @param bool $isGoogleError google登入是否錯誤
 * @param int|null $activatedStatus 啟動狀態
 */
function login_form(bool $isGoogleError = false, int $activatedStatus = null) {

    /* alert for GET method */
    $msg = $isGoogleError ? '<div class="alert alert-danger" role="alert">' . showText("Login.AUTH_GOOGLE_ERROR") . '</div>' : '';
    if ($activatedStatus == AUTH_REGISTER_CODE_WRONG) $msg = '<div class="alert alert-danger" role="alert">' . showText("Login.REGISTER_CODE_WRONG") . '</div>';
    if ($activatedStatus == AUTH_SERVER_ERROR) $msg = '<div class="alert alert-danger" role="alert">' . showText("Error") . '</div>';
    if ($activatedStatus == AUTH_REGISTER_COMPLETE) $msg = '<div class="alert alert-success" role="alert">' . showText("Login.REGISTER_COMPLETE") . '</div>';

    //指引文字
    $Text = showText("Login");

    echo <<<LOGIN_FROM
<!-- login area start -->
<div class="login-area login-bg">
    <div class="container">
        <div class="login-box ptb--100">
            <form class="needs-validation" novalidate>
                <div class="login-form-head">
                    <h4>{$Text['Login']}</h4>
                    <p>{$Text['welcome']}</p>
                </div>
                <div id="ResultMsg">
                {$msg}
                </div>
                <div class="login-form-body">
                    <div class="form-gp focused">
                        <label for="Email">{$Text['email']}</label>
                        <input type="email" class="form-control" autocomplete="username" required="required" name="email" autofocus inputmode="email" pattern="^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$">
                        <i class="ti-email"></i>
                        <div class="invalid-feedback">{$Text['Form']['Error_format']}</div>
                    </div>
                    <div class="form-gp">
                        <label for="Password">{$Text['password']}</label>
                        <input type="password" class="form-control" autocomplete="current-password" required="required" id="Password" name="password">
                        <i class="ti-lock"></i>
                        <div class="invalid-feedback">{$Text['Form']['Cant_EMPTY']}</div>
                    </div>
                    <div class="row mb-4 rmber-area">
                         <div class="col-6">
                            <div class="form-check mr-sm-2">
                                    <input type="checkbox" class="form-check-input" id="Remember_ME" name="remember_me">
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

/**
 * 狀態結果訊息
 * @param int $type 類型
 * @return string 訊息
 */
function ResultMsg(int $type): string {
    switch ($type) {
        case AUTH_WRONG_PASS:
            return showText("Login.WRONG_PASS");
        case AUTH_NOT_DONE:
            return showText("Login.NOT_DOEN");
        case AUTH_BLOCK_10:
            return showText("Login.BLOCK_10.0") . '<br>' . showText("Login.BLOCK_10.1");
        case AUTH_BLOCK_30:
            return showText("Login.BLOCK_30.0") . '<br>' . showText("Login.BLOCK_30.1");
        case AUTH_SERVER_ERROR:
            return showText("Error");
        default:
            return '';
    }
}

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
    "myself/login",
    "jquery", 
    "bootstrap", 
    "owl.carousel.min",
    "metisMenu.min",
    "jquery.slimscroll.min",
    "jquery.slicknav.min",
    "plugins",
    "scripts",
    "forge"], () => {
        $(window).on('load', function() {
            $('#preloader').fadeOut('slow', function() { $(this).remove(); });
        });
    })
</script>
<script src="/panel/assets/js/sw-register.min.js"></script>
</body>
</html>
Foot;

/**
 * 載入google客户端
 * @return Google_Client google客户端
 * @throws \Google\Exception
 */
function load_google_client(): Google_Client {
    //require_once('vendor/autoload.php');

    $gclient = new Google_Client();
    $gclient->setAuthConfig('function/secret/credentials.json');
    $gclient->setAccessType('offline'); // offline access
    $gclient->setIncludeGrantedScopes(true); // incremental auth
    $gclient->addScope([Google_Service_Oauth2::USERINFO_EMAIL, Google_Service_Oauth2::USERINFO_PROFILE]);
    $gclient->setRedirectUri('https://itp4506.cocopixelmc.com/panel/login?login=google');

    return $gclient;
}
