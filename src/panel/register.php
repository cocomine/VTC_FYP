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

/* header */
const title = "Register.title";
require_once('./stable/header.php'); //head

$auth = new MyAuth(Cfg_Sql_Host, Cfg_Sql_dbName, Cfg_Sql_dbUser, Cfg_Sql_dbPass, Cfg_500_Error_File_Path, Cfg_Cookies_Path); //startup
$auth->checkAuth(); //start auth

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    /* post 請求 */
    ob_clean();
    header("content-type: text/json; charset=UTF-8"); //is json
    $data = json_decode(file_get_contents("php://input"), true);

    //已經登入
    if ($auth->islogin) {
        ob_clean();
        echo json_encode(array(
            'code' => AUTH_REGISTER_COMPLETE,
            'Message' => ResultMsg(AUTH_REGISTER_COMPLETE)
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

    /* 傳送註冊 */
    $auth->add_Hook('acc_register', 'acc_Activated_Mail_Hook');
    $status = $auth->register($data['name'], $data['password2'], $data['password'], $data['email'],
        $localCode ?? 'en', $data["g-recaptcha-response"], Cfg_recaptcha_key, $_SESSION['pvKey']);
    echo json_encode(array(
        'code' => $status,
        'Message' => ResultMsg($status),
    ));
    exit();
} else {
    /* get 請求 */
    //已經登入
    if ($auth->islogin) {
        ob_clean();
        header("Location: /panel");
        exit();
    }

    //未登入
    if (isset($_GET['email']) && isset($_GET['name'])) {
        $email = filter_var(trim($_GET['email'] ?? ""), FILTER_SANITIZE_EMAIL);
        $name = filter_var(trim($_GET['name'] ?? ""), FILTER_SANITIZE_STRING);
        register_form($email, $name);//Auto Fill
    } else {
        register_form();//預設
    }
}

/**
 *註冊表
 *
 * @param string|null $email 電郵地址
 * @param string|null $name 名稱
 */
function register_form(string $email = null, string $name = null) {

    //首次使用google
    $msg = $email != null ? '<div class="alert alert-info" role="alert">' . showText("Register.First_use") . '</div>' : '';

    //語言
    $Text = showText("Register");

    //js使用
    $LangJson = json_encode(array(
        'strength' => showText("Register.strength")
    ));

    echo <<<REGISTER_FROM
    <!-- login area start -->
    <div class="login-area login-bg register-bg">
        <div class="container-fluid p-0">
            <div class="row no-gutters">
                <div class="col-xl-4 offset-xl-8 col-lg-6 offset-lg-6">
                    <div class="login-box-s2 ptb--100">
                        <form class="" novalidate>
                            <div class="login-form-head">
                                <h4>{$Text['Register']}</h4>
                                <p>{$Text['welcome']}</p>
                            </div>
                            <div id="ResultMsg">
                                {$msg}
                            </div>
                            <div class="login-form-body">
                                <div class="form-gp focused">
                                    <label for="Name">{$Text['Name']}</label>
                                    <input type="text" class="form-control" name="name" id="Name" required="required" autocomplete='nickname' autofocus value='{$name}' maxlength="16">
                                    <i class="ti-user"></i>
                                    <div class="invalid-feedback">
                                        {$Text['Form']['Cant_EMPTY']}
                                    </div>
                                </div>
                                <div class="form-gp">
                                    <label for="Email">{$Text['email']}</label>
                                    <input type="email" class="form-control" autocomplete="username" id="Email" name="email" required="required" value="{$email}" inputmode="email" pattern="^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$">
                                    <i class="ti-email"></i>
                                    <div class="invalid-feedback">
                                        {$Text['Form']['Error_format']}
                                    </div>
                                </div>
                                <div class="form-gp">
                                    <label for="Password">{$Text['password']}</label>
                                    <input type="password" class="form-control" autocomplete="new-password" id="Password" pattern='(?=.*?[A-Z])(?=.*?[a-z]).{8,}' required="required" name="password">
                                    <i class="ti-lock"></i>
                                    <div class="invalid-feedback">
                                        {$Text['Form']['Cant_EMPTY']}
                                    </div>
                                </div>
                                <div class="form-gp">
                                    <label for="Password2">{$Text['confirmPass']}</label>
                                    <input type="password" class="form-control" autocomplete="new-password" required="required" id="Password2" pattern='(?=.*?[A-Z])(?=.*?[a-z]).{8,}' name="password2">
                                    <i class="ti-lock"></i>
                                    <div class="invalid-feedback">
                                        {$Text['Form']['Not_Match_Wrong']}
                                    </div>
                                </div>
                                <div class="form-gp">
                                    <p>
                                        {$Text['passStrength']} 
                                        <div class="progress">
                                            <div class="progress-bar" role="progressbar" style="width: 0%" id="passStrength"></div>
                                        </div>
                                    </p>
                                    <p>
                                        <b>{$Text['condition'][0]}</b>
                                        <ol id="passStrength-list">
                                            <li>{$Text['condition'][1]}</li>
                                            <li>{$Text['condition'][2]}</li>
                                            <li>{$Text['condition'][3]}</li>
                                            <li>{$Text['condition'][4]}</li>
                                        </ol>
                                    </p>
                                </div>
                                <br>
                                <div id="g-recaptcha" class="g-recaptcha form-control" data-sitekey="6Le90ykTAAAAAOgxgMUBE-hW7OivFZs1ebR5btuu" data-callback="recaptchacall"></div>
                                <div class="invalid-feedback">
                                        {$Text['Form']['Check_bot']}
                                </div>
                                <br><br>
                                <div class="submit-btn-area">
                                    <button id="form_submit" type="submit">{$Text['Register']} <i class="ti-arrow-right"></i></button>
                                </div>
                                <div class="form-footer text-center mt-5">
                                    <p class="text-muted">{$Text['go_login']}</p>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- login area end -->
    <pre style="display: none" id="langJson">
        {$LangJson}
    </pre>
REGISTER_FROM;
}

/**
 * 狀態結果訊息
 * @param int $type 類型
 * @return string 訊息
 */
function ResultMsg(int $type): string {
    switch ($type) {
        case AUTH_REGISTER_EMAIL_FAIL:
            return showText("Register.EMAIL_FAIL");
        case AUTH_REGISTER_PASS_NOT_MATCH:
            return showText("Register.PASS_NOT_MATCH");
        case AUTH_REGISTER_PASS_NOT_STRONG:
            return showText("Register.PASS_NOT_STRONG");
        case AUTH_REGISTER_EMAIL_WRONG_FORMAT:
            return showText("Register.EMAIL_WRONG_FORMAT");
        case AUTH_REGISTER_EMPTY:
            return showText("Register.EMPTY");
        case AUTH_REGISTER_NAME_TOO_LONG:
            return showText('Register.NAME_TOO_LONG');
        case AUTH_REGISTER_LAST_STEP:
            return showText("Register.LAST_STEP");
        case AUTH_REGISTER_YOUR_BOT:
            return showText("Register.YOUR_BOT");
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
            zxcvbn: "https://cdnjs.cloudflare.com/ajax/libs/zxcvbn/4.4.2/zxcvbn",
            grecaptcha: "https://www.google.com/recaptcha/api",
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
    "myself/register",
    "grecaptcha",
    "jquery", 
    "bootstrap", 
    "owl.carousel.min",
    "metisMenu.min",
    "jquery.slimscroll.min",
    "jquery.slicknav.min",
    "plugins",
    "scripts",
    "zxcvbn",
    "forge"
    ], (register) => window.recaptchacall = register.recaptchacall)
</script>
<script src="/panel/assets/js/sw-register.min.js"></script>
</body>
</html>
Foot;