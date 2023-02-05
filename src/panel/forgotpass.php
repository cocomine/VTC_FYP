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

use cocomine\MyAuth;
use cocomine\MyAuthException;

/* header */
const title = "ForgotPass.title";
require_once('./stable/header.php'); //head

//start auth
$auth = new MyAuth(Cfg_Sql_Host, Cfg_Sql_dbName, Cfg_Sql_dbUser, Cfg_Sql_dbPass, Cfg_Cookies_Path); //startup
try {
    $auth->checkAuth();
} catch (MyAuthException $e) {
    ob_clean();
    http_response_code(500);
    require(Cfg_500_Error_File_Path);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    /* POST 請求 */
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

    if (isset($_SESSION['Doing_Reset'])) {
        $status = $auth->ForgetPass_set($data['password'] ?? "", $data['password2'] ?? "", $_SESSION['pvKey']);//傳送新密碼
    } else {
        $auth->add_Hook('acc_ForgetPass', 'acc_ForgetPass_Hook');
        $status = $auth->ForgetPass($data['email'] ?? "", $data['g-recaptcha-response'], Cfg_recaptcha_key);//傳送電郵
    }
    echo json_encode(array(
        'code' => $status,
        'Message' => ResultMsg($status)
    ));
    exit();
} else {
    /* GET 請求 */

    /* 確認登入 */
    if ($auth->islogin) {
        ob_clean();
        header("Location: /panel");
        exit();
    }

    /* 修改密碼 */
    if (!empty($_GET['code'])) {
        $status = $auth->ForgetPass_Confirm($_GET['code']);
        if ($status == AUTH_FORGETPASS_CODE_OK) {
            $_SESSION['Doing_Reset'] = true;
            ForgetPassSet_from();
        } else {
            ForgetPass_from(true);
            unset($_SESSION['Doing_Reset']);
        }
    }

    /* 正常訪問 */
    if (empty($_GET['code'])) {
        ForgetPass_from();
        unset($_SESSION['Doing_Reset']);
    }
}

/**
 * 忘記密碼表
 *
 */
function ForgetPass_from(bool $isCodeWong = false) {

    $msg = $isCodeWong ? '<div class="alert alert-info" role="alert">' . showText("ForgotPass.CODE_WRONG") . '</div>' : '';

    //指引文字
    $Text = showText("ForgotPass");

    echo <<<FORGETPASS_FROM
<div class="login-area login-bg">
    <div class="container">
        <div class="login-box ptb--100">
            <form class="needs-validation" novalidate id="EmailStep">
                <div class="login-form-head">
                    <h4>{$Text['ForgotPass']}</h4>
                    <p>{$Text['welcome']}</p>
                </div>
                <div id="ResultMsg">$msg</div>
                <div class="login-form-body">
                    <div class="form-gp focused">
                        <label for="Email">{$Text['Email']}</label>
                        <input type="email" class="form-control" autocomplete="username" required="required" name="email" title="{$Text['Email_Tips']}" autofocus inputmode="email" pattern="^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$">
                        <i class="ti-email"></i>
                        <div class="invalid-feedback">{$Text['Form']['Error_format']}</div>
                    </div>
                    <div id="g-recaptcha" class="g-recaptcha form-control" data-sitekey="6Le90ykTAAAAAOgxgMUBE-hW7OivFZs1ebR5btuu" data-callback="recaptchacall"></div>
                    <div class="invalid-feedback">{$Text['Form']['Check_bot']}</div>
                    <div class="submit-btn-area mt-5">
                        <button id="form_submit" type="submit">{$Text['FindPass']} <i class="ti-arrow-right"></i></button>
                    </div>
                    <div class="form-footer text-center mt-5">
                        <p class="text-muted">{$Text['go_login']}</p>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<pre style="display: none" id="langJson">{}</pre>
FORGETPASS_FROM;
}

/**
 * 忘記密碼修改密碼表
 */
function ForgetPassSet_from() {

    $Text = showText("ForgotPass");

    //js使用
    $LangJson = json_encode(array(
        'strength' => showText("Register.strength")
    ));

    echo <<<FORGETPASSSET_FROM
<div class="login-area login-bg">
    <div class="container">
        <div class="login-box ptb--100">
            <form class="needs-validation" novalidate id="PasswordStep">
                <div class="login-form-head">
                    <h4>{$Text['ForgotPass']}</h4>
                    <p>{$Text['welcome2']}</p>
                </div>
                <div id="ResultMsg"></div>
                <div class="login-form-body">
                    <div class="form-gp focused">
                        <label for="Password">{$Text['NewPass']}</label>
                        <input type="password" class="form-control" autocomplete="new-password" id="Password" required="required" autofocus name="password">
                        <i class="ti-lock"></i>
                        <div class="invalid-feedback">{$Text['Form']['Cant_EMPTY']}</div>
                    </div>
                    <div class="form-gp">
                        <label for="Password2">{$Text['ConfirmNewPass']}</label>
                        <input type="password" class="form-control" autocomplete="new-password" required="required" id="Password2" name="password2">
                        <i class="ti-lock"></i>
                        <div class="invalid-feedback">{$Text['Form']['Not_Match_Wrong']}</div>
                    </div>
                    <div class="form-gp">
                        <p>
                            {$Text['passStrength']} 
                            <div class="progress">
                                <div class="progress-bar" role="progressbar" style="width: 0" id="passStrength"></div>
                            </div>
                        </p>
                        <p>
                            <b>{$Text['condition'][0]}</b>
                            <ol id="passStrength-list">
                                <li><span class='status-p bg-danger'>{$Text['condition'][1]}</span></li>
                                <li><span class='status-p bg-danger'>{$Text['condition'][2]}</span></li>
                                <li><span class='status-p bg-danger'>{$Text['condition'][3]}</span></li>
                                <li><span class='status-p bg-danger'>{$Text['condition'][4]}</span></li>
                            </ol>
                        </p>
                    </div>
                    <div class="submit-btn-area mt-5">
                        <button id="form_submit" type="submit">{$Text['Change_Pass']} <i class="ti-arrow-right"></i></button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<pre style="display: none" id="langJson">
    $LangJson
</pre>
FORGETPASSSET_FROM;

}

/**
 * 結果訊息
 * @param int $type 類型
 * @return string 訊息
 */
function ResultMsg(int $type): string {
    switch ($type) {
        case AUTH_FORGETPASS_EMAIL_FAIL:
            return showText("ForgotPass.EMAIL_FAIL");
        case AUTH_FORGETPASS_LASTSTEP:
            return showText("ForgotPass.LASTSTEP");
        case AUTH_FORGETPASS_CODE_WRONG:
            return showText("ForgotPass.CODE_WRONG");
        case AUTH_FORGETPASS_COMPLETE:
            return showText("ForgotPass.COMPLETE");
        case AUTH_FORGETPASS_YOUR_BOT:
            return showText("ForgotPass.YOUR_BOT");
        case AUTH_FORGETPASS_EMPTY:
            return showText('ForgotPass.EMPTY');
        case AUTH_FORGETPASS_PASS_NOT_MATCH:
            return showText("ForgotPass.PASS_NOT_MATCH");
        case AUTH_FORGETPASS_PASS_NOT_STRONG:
            return showText("ForgotPass.PASS_NOT_STRONG");
        default:
            return '';
    }
}

echo <<<Foot
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
                deps: ["jquery"]
            }
        }
});
require([
    "myself/forgotpass",
    "grecaptcha",
    "jquery", 
    "bootstrap", 
    "plugins",
    "scripts",
    "zxcvbn",
    "forge"
    ], (forgotpass) => {
        window.recaptchacall = forgotpass.recaptchacall;
        $('#preloader').fadeOut('slow', function() { $(this).remove(); });
    });
</script>
<script src="/panel/assets/js/sw-register.min.js"></script>
</body>
</html>
Foot;