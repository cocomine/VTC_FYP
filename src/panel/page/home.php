<?php
/**
 * Copyright (c) 2020.
 * Create by cocomine
 */

namespace panel\page;

use cocomine\IPage;
use mysqli;

/**
 * Class home
 * @package cocopixelmc\Page
 */
class home implements IPage {
    private mysqli $sqlcon;

    /**
     * home constructor.
     * sql連接
     * @param $sqlcon
     */
    function __construct($sqlcon) {
        $this->sqlcon = $sqlcon;
    }

    /* 是否有權進入 */
    function access(bool $isAuth, int $role, bool $isPost): int {
        return 200;
    }

    /* 輸出頁面 */
    function showPage(): string {

        $Text = showText('index.Content');

        /* json 語言 */
        $jsonLang = json_encode(array(
            'strength' => showText('ChangeSetting.strength'),
        ));

        return <<<body
<pre id='langJson' style='display: none'>$jsonLang</pre>
<link rel="stylesheet" href="/panel/assets/css/myself/datetimepicker.css">
<div class='col-12 mt-4' style="height: 100vh">
    <div class="card h-100" style="background-image: url('/panel/assets/images/bg/bg/6.webp'); background-size: cover; background-position: center">
        <div class='card-body'>
        <form class="needs-validation h-100" novalidate id="search">
            <div class="row align-content-center h-100 text-light g-2">
                <div class="col-12">
                    <h1>{$Text['Title']}</h1>
                </div>
                <div class="col-12 col-lg-10 col-xxl-8">
                    <div class="row align-items-center g-2 justify-content-center">
                        <div class="input-group col-12 col-md ps-1">
                            <span class="input-group-text form-rounded"><i class="fa-solid fa-plane-departure ps-1"></i></span>
                            <div class="form-floating">
                                <input type="text" class="form-control form-rounded" list="departure-list" id="Departure" name="departure" placeholder="{$Text['Departure']}" required>
                                <datalist id="departure-list">
                                    <option value="Hong Kong International Airport">
                                    <option value="Kansai Airports">
                                    <option value="Shanghai Pudong International Airport">
                                    <option value="Taoyuan International Airport">
                                </datalist>
                                <label for="Departure">{$Text['Departure']}</label>
                                <div class="invalid-tooltip">{$Text['Form']['Cant_EMPTY']}</div>
                            </div>
                        </div>
                        <div class="col-auto">
                            <button class="btn btn-light btn-rounded" id="reverse" type="button"><i class="fa-solid fa-arrow-right-arrow-left"></i></button>
                        </div>
                        <div class="input-group col-12 col-md ps-1">
                            <span class="input-group-text form-rounded"><i class="fa-solid fa-plane-arrival ps-1"></i></span>
                            <div class="form-floating">
                                <input type="text" class="form-control form-rounded" list="destination-list" id="Destination" name="destination" placeholder="{$Text['Destination']}" required>
                                <datalist id="destination-list">
                                    <option value="Hong Kong International Airport">
                                    <option value="Kansai Airports">
                                    <option value="Shanghai Pudong International Airport">
                                    <option value="Taoyuan International Airport">
                                </datalist>
                                <label for="Destination">{$Text['Destination']}</label>
                                <div class="invalid-tooltip">{$Text['Form']['Cant_EMPTY']}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-10 col-xxl-8">
                    <div class="row align-items-center g-2">
                        <div class="input-group col-12 col-md ps-1">
                            <span class="input-group-text form-rounded"><i class="fa-regular fa-calendar ps-1"></i></span>
                            <div class="form-floating date-picker">
                                <input type="date" id="Date" name="date" class="form-control form-rounded date-picker-toggle" data-bs-toggle="dropdown" placeholder="{$Text['Date']}" required>
                                <label for="Date">{$Text['Date']}</label>
                                <div class="invalid-tooltip">{$Text['Form']['min_date']}</div>
                            </div>
                        </div>
                        <div class="input-group col-12 col-md ps-1">
                            <span class="input-group-text form-rounded"><i class="fa-solid fa-briefcase"></i></span>
                            <div class="form-floating">
                                <select class="form-select form-rounded" aria-label="Default select example" id="Cabin" name="cabin" required>
                                    <option value="0">{$Text['Cabin_type'][0]}</option>
                                    <option value="1">{$Text['Cabin_type'][1]}</option>
                                    <option value="2">{$Text['Cabin_type'][2]}</option>
                                </select>
                                <label for="Cabin">{$Text['Cabin']}</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-10 col-xxl-8">
                    <div class="row justify-content-end g-2">
                        <div class="col col-lg-6">
                            <button class="btn btn-primary btn-rounded w-100 border border-2 border-light form-submit" type="submit"><i class="fa-solid fa-magnifying-glass me-2"></i>{$Text['Search']}</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </form>
    </div>
</div>
<div id='SetPass' class='modal fade' data-bs-backdrop='static' data-bs-keyboard='false' tabindex='-1'>
    <div class='modal-dialog modal-dialog-centered'>
        <div class='modal-content'>
            <div class='modal-header'>
                <h5 class='modal-title'><b>Set new password</b></h5>
            </div>
            <form id='PassSet' novalidate class='needs-validation'>
                <div class='modal-body'>
                    <p>Since you are logging in for the first time and using the default password, please change your password first.</p>
                    <hr>
                    <div class='col-12'>
                        <label for='New_Pass' class='col-form-label'>{$Text['Pass']['NewPass']}</label>
                        <input class='input-rounded form-control' type='password' id='Password' pattern='(?=.*?[A-Z])(?=.*?[a-z]).{8,}' name='password' autocomplete='new-password' required>
                        <div class='invalid-feedback'>{$Text['Form']['Cant_EMPTY']}</div>
                    </div>
                    <div class='col-12'>
                        <label for='CPass' class='col-form-label'>{$Text['Pass']['NewConfirmPass']}</label>
                        <input class='form-control input-rounded' type='password' id='Password2' pattern='(?=.*?[A-Z])(?=.*?[a-z]).{8,}' name='password2' autocomplete='new-password' required data-placement='auto' data-toggle='popover' data-html='true' data-trigger='manual' data-content='<i class=\"ti-alert\" style=\"color:red;\"></i> " . showText("ChangeSetting.Pass.NotMach") . "'>
                        <div class='invalid-feedback'>{$Text['Form']['Not_Match_Wrong']}</div>
                    </div>
                    <div class='col-12 mt-4'>
                        <p>
                            {$Text['Pass']['passStrength']}
                            <div class='progress'>
                                <div class='progress-bar' role='progressbar' style='width: 0' id='passStrength'></div>
                            </div>
                        </p>
                        <p>
                            <b>{$Text['Pass']['condition'][0]}</b>
                            <ol id='passStrength-list'>
                                <li><span class='status-p bg-danger'>{$Text['Pass']['condition'][1]}</span></li>
                                <li><span class='status-p bg-danger'>{$Text['Pass']['condition'][2]}</span></li>
                                <li><span class='status-p bg-danger'>{$Text['Pass']['condition'][3]}</span></li>
                                <li><span class='status-p bg-danger'>{$Text['Pass']['condition'][4]}</span></li>
                            </ol>
                        </p>
                    </div>
                </div>
                <div class='modal-footer'>
                    <button type='submit' class='btn btn-rounded btn-primary form-submit'><i class='fa fa-save pe-2'></i>Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
require.config({
    paths:{
        forge: ['https://cdn.jsdelivr.net/npm/node-forge/dist/forge.min'],
        zxcvbn: ['https://cdnjs.cloudflare.com/ajax/libs/zxcvbn/4.4.2/zxcvbn'],
    },
});
loadModules(['moment.min', 'myself/datatimepicker', 'myself/page/home', 'forge'])
</script>
body;
    }

    /* POST請求 */
    function post(array $data): array {
        global $auth;

        if($_GET['type'] == 'PassSet'){
            $status = $auth->changePasswordSetting($data['passwordOld'], $data['password'], $data['password2'], $_SESSION['pvKey']);

            if($status == AUTH_CHANGESETTING_PASS_OK){
                $stmt = $this->sqlcon->prepare("DELETE FROM pwd_change WHERE UUID = ?");
                $stmt->bind_param('s', $auth->userdata['UUID']);
                $stmt->execute();
            }

            return array(
                'code' => $status,
                'Title' => $this->ResultMsg($status)[0],
                'Message' => $this->ResultMsg($status)[1],
            );
        }

        /* 是否需要強制更改密碼 */
        $stmt = $this->sqlcon->prepare("SELECT * FROM pwd_change WHERE UUID = ?");
        $stmt->bind_param('s', $auth->userdata['UUID']);
        if (!$stmt->execute()) return array('state' => false);

        $result = $stmt->get_result();
        if ($result->num_rows > 0) return array('state' => true);

        return array('state' => false);
    }

    /* path輸出 */
    function path(): string {
        return "<li><span>" . showText("index.home") . "</span></li>";
    }

    /* 取得頁面標題 */
    public function get_Title(): string {
        return showText('index.title');
    }

    /* 取得頁首標題 */
    public function get_Head(): string {
        return showText("index.home");
    }

    /**
     * 翻譯結果訊息
     * @param int $type 類型
     * @return string[] 訊息
     */
    private function ResultMsg(int $type): array {
        switch ($type) {
            case AUTH_CHANGESETTING_PASS_FAIL_EMPTY:
                return showText('ChangeSetting.AUTH_CHANGESETTING_PASS_FAIL_EMPTY');
            case AUTH_CHANGESETTING_PASS_OK:
                return showText('ChangeSetting.AUTH_CHANGESETTING_PASS_OK');
            case AUTH_CHANGESETTING_PASS_FAIL:
                return showText('ChangeSetting.AUTH_CHANGESETTING_PASS_FAIL');
            case AUTH_CHANGESETTING_PASS_FAIL_NOT_MATCH:
                return showText('ChangeSetting.AUTH_CHANGESETTING_PASS_FAIL_NOT_MATCH');
            case AUTH_CHANGESETTING_PASS_FAIL_NOT_STRONG:
                return showText('ChangeSetting.AUTH_CHANGESETTING_PASS_FAIL_NOT_STRONG');
            case AUTH_CHANGESETTING_PASS_FAIL_OLD_PASS_WRONG:
                return showText('ChangeSetting.AUTH_CHANGESETTING_PASS_FAIL_OLD_PASS_WRONG');
            default:
                return array('', '');
        }
    }
}