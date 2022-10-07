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

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    if(!$auth->islogin){
        /* 傳送註冊 */
        $auth->add_Hook('acc_register', 'acc_Activated_Mail_Hook');
        $auth->register($_POST['Name'] ?? "", $_POST['confirm_pass'] ?? "", $_POST['password'] ?? "", $_POST['Email'] ?? "", $_POST['Agree-Service'] ?? "", $_POST['Agree-privacy'] ?? "", $localCode ?? 'en');
    }else{
        ob_clean();
        header("Location: /panel");
        exit();
    }
}else{
    if($auth->islogin) {
        ob_clean();
        header("Location: /panel");
        exit();
    }else{
        if(isset($_GET['email']) && isset($_GET['name'])){
            $email = filter_var(trim($_GET['email'] ?? ""), FILTER_SANITIZE_EMAIL);
            $name = filter_var(trim($_GET['name'] ?? ""), FILTER_SANITIZE_STRING);
            register_form(AUTH_REGISTER_FIRST_GOOGLE, $email, $name);//Auto Fill
        }else{
            register_form();//預設
        }
    }
}

/**
 *註冊表
 *
 * @param int|null $status register status
 * @param string|null $email 電郵地址
 * @param string|null $name 名稱
 */
function register_form(int $status = null, string $email = null, string $name = null) {
    switch ($status) {
        case AUTH_REGISTER_EMAIL_FAIL:
            $msg = '<div class="alert alert-danger" role="alert">
                        ' . showText("Register.EMAIL_FAIL") . '
                    </div>';
            break;
        case AUTH_REGISTER_PASS_NOT_MATCH:
            $msg = '<div class="alert alert-danger" role="alert">
                        ' . showText("Register.PASS_NOT_MATCH") . '
                    </div>';
            break;
        case AUTH_REGISTER_PASS_NOT_STRONG:
            $msg = '<div class="alert alert-danger" role="alert">
                        ' . showText("Register.PASS_NOT_STRONG") . '
                    </div>';
            break;
        case AUTH_REGISTER_EMAIL_WRONG_FORMAT:
            $msg = '<div class="alert alert-danger" role="alert">
                        ' . showText("Register.EMAIL_WRONG_FORMAT") . '
                    </div>';
            break;
        case AUTH_REGISTER_EMPTY:
            $msg = '<div class="alert alert-danger" role="alert">
                        ' . showText("Register.EMPTY") . '
                    </div>';
            break;
        case AUTH_REGISTER_NAME_TOO_LONG:
            $msg = '<div class="alert alert-danger" role="alert">
                        ' . showText("Register.NAME_TOO_LONG") . '
                    </div>';
            break;
        case AUTH_REGISTER_LAST_STEP:
            $msg = '<div class="alert alert-primary" role="alert">
                        ' . showText("Register.LAST_STEP") . '
                    </div>';
            break;
        case AUTH_REGISTER_NEED_AGREE:
            $msg = '<div class="alert alert-danger" role="alert">
                        ' . showText("Register.NEED_AGREE") . '
                    </div>';
            break;
        case AUTH_REGISTER_YOUR_BOT:
            $msg = '<div class="alert alert-danger" role="alert">
                        ' . showText("Register.YOUR_BOT") . '
                    </div>';
            break;
        case AUTH_REGISTER_FIRST_GOOGLE:
            $msg = '<div class="alert alert-info" role="alert"><b>你是第一次使用!</b> 註冊了之後就可以直接使用google登入啦</div>';
            break;
        default:
            $msg = '';
            break;
    }

    $Text = array( //指引文字
        "Register" => showText("Register.Register"),
        "welcome" => showText("Register.welcome"),
        "Name" => showText("Register.Name"),
        "email" => showText("Register.email"),
        "password" => showText("Register.password"),
        "confirmPass" => showText("Register.confirmPass"),
        "Not_Match_Wrong" => showText("Register.Not_Match_Wrong"),
        "passStrength" => showText("Register.passStrength"),
        "condition0" => showText("Register.condition.0"),
        "condition1" => showText("Register.condition.1"),
        "condition2" => showText("Register.condition.2"),
        "condition3" => showText("Register.condition.3"),
        "condition4" => showText("Register.condition.4"),
        "Agree-Service" => showText("Register.Agree-Service"),
        "Agree-privacy" => showText("Register.Agree-privacy"),
        "go_login" => showText("Register.go_login")
    );

    echo <<<REGISTER_FROM
    <!-- login area start -->
    <div class="login-area login-bg register-bg">
        <div class="container-fluid p-0">
            <div class="row no-gutters">
                <div class="col-xl-4 offset-xl-8 col-lg-6 offset-lg-6">
                    <div class="login-box-s2 ptb--100">
                        <form action="/panel/register" method="post">
                            <div class="login-form-head">
                                <h4>{$Text['Register']}</h4>
                                <p>{$Text['welcome']}</p>
                            </div>
                            {$msg}
                            <div class="login-form-body">
                                <div class="form-gp focused">
                                    <label for="Name">{$Text['Name']}</label>
                                    <input type="text" name="Name" required="required" id="Name" autocomplete='nickname' autofocus value='{$name}' maxlength="16">
                                    <i class="ti-user"></i>
                                </div>
                                <div class="form-gp">
                                    <label for="Email">{$Text['email']}</label>
                                    <input type="email" autocomplete="username" name="Email" required="required" id="Email" value="{$email}" inputmode="email">
                                    <i class="ti-email"></i>
                                </div>
                                <div class="form-gp">
                                    <label for="Password">{$Text['password']}</label>
                                    <input type="password" autocomplete="new-password" id="Password" pattern='(?=.*?[A-Z])(?=.*?[a-z]).{8,}' required="required">
                                    <i class="ti-lock"></i>
                                </div>
                                <div class="form-gp">
                                    <label for="Password2">{$Text['confirmPass']}</label>
                                    <input type="password" autocomplete="new-password" required="required" id="Password2" pattern='(?=.*?[A-Z])(?=.*?[a-z]).{8,}' data-placement="auto" data-toggle="popover" data-html="true" data-trigger="manual" data-content="<i class='ti-alert' style='color:red;'></i> {$Text['Not_Match_Wrong']}">
                                    <i class="ti-lock"></i>
                                </div>
                                <div class="form-gp">
                                    <p>
                                        {$Text['passStrength']} <span id='password-strength-text'>--</span> <br> 
                                        <meter max='5' value='1' id='password-strength-meter'></meter>
                                    </p>
                                    <p>
                                        <b>{$Text['condition0']}</b><br>
                                        1. {$Text['condition1']}<br>
                                        2. {$Text['condition2']}<br>
                                        3. {$Text['condition3']}<br>
                                        4. {$Text['condition4']}
                                    </p>
                                </div>
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="Agree-Service" name="Agree-Service" required="required">
                                    <label class="custom-control-label" for="Agree-Service">{$Text['Agree-Service']}</label>
                                </div>
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="Agree-privacy" name="Agree-privacy" required="required">
                                    <label class="custom-control-label" for="Agree-privacy">{$Text['Agree-privacy']}</label>
                                </div>
                                <br>
                                <div id="g-recaptcha" class="g-recaptcha" data-sitekey="6Le90ykTAAAAAOgxgMUBE-hW7OivFZs1ebR5btuu" data-callback="recaptchacall"></div>
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
REGISTER_FROM;
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
            zxcvbn: "https://cdnjs.cloudflare.com/ajax/libs/zxcvbn/4.4.2/zxcvbn"
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
    "scripts",
    "zxcvbn"])
</script>
<script src="/panel/assets/js/sw-register.min.js"></script>
<!--others js-->
<script src="/panel/assets/js/myself/password-strength-meter.min.js"></script>
<script src="/panel/assets/js/jsencrypt.min.js"></script>
<script src="/panel/assets/js/myself/pass-encrypt.min.js"></script>
<script src="/panel/assets/js/myself/Form-submit-loadicon.min.js"></script>
<script src="/panel/assets/js/myself/js-Lang.min.js"></script>
</body>
</html>
Foot;