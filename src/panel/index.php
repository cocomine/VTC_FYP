<?php
/*
 * Copyright (c) 2020.
 * Create by cocomine
 */

/**
 * Created by IntelliJ IDEA.
 * User: user
 * Date: 10/12/2018
 * Time: 下午 9:03
 */

use cocomine\MyAuth;
use panel\page\home;

/* header */
const title = "index.title";
require_once('./stable/header.php');

static $auth;
$auth = new MyAuth(Cfg_Sql_Host, Cfg_Sql_dbName, Cfg_Sql_dbUser, Cfg_Sql_dbPass, Cfg_500_Error_File_Path, Cfg_Cookies_Path); //startup
$auth->checkAuth(); //start auth
$_SERVER['HTTP_X_REQUESTED_WITH'] = strtolower(@$_SERVER['HTTP_X_REQUESTED_WITH']);

/* API互動介面 (即係唔係俾人睇) */
//if (isset($_GET['api'])) {
//    switch ($_GET['api']) {
//        case 'notifyAPI':
//            /* 通知API */
//            require_once('./function/notifyAPI.php');
//            $notifyAPI = new notifyAPI($auth->sqlcon);
//            $output = $notifyAPI->Show_notify($_SESSION['UUID'], 20);
//            break;
//        default:
//            $output = array(
//                'error' => 'Request not include request API'
//            );
//            break;
//    }
//}

/* AJAX內容 */
if ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'xmlhttprequest') {
    ob_clean();
    header("content-type: text/json; charset=utf-8");

    // 消毒/分割
    $path = strtolower(filter_var(trim($_GET['p']), FILTER_SANITIZE_STRING));
    $path = explode("/", $path);

    //清除多餘數組
    if ($path[count($path) - 1] === "") {
        $path = array_slice($path, 0, -1);
    }

    //輸出頁面 home 頁面
    if (count($path) < 1) {
        require_once('./page/home.php');
        $homePage = new home($auth->sqlcon);

        //檢查權限
        $access = $homePage->access($auth->islogin, $auth->userdata['Role'] ?? 0);
        if ($access == 200) {
            //頁面輸出
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                echo json_encode($homePage->post());
            } else {
                echo json_encode(array(
                    'title' => $homePage->get_Title(),
                    'head' => $homePage->get_Head(),
                    'path' => $homePage->path(),
                    'content' => $homePage->showPage()
                ));
            }
        } else if ($access == 403) {
            //沒有權限
            http_response_code(403);
            echo json_encode(array('code' => 403, 'Message' => showText("Error_Page.Dont_Come")));
        } else if ($access == 401) {
            //需要登入
            http_response_code(401);
            echo json_encode(array('code' => 401, 'path' => './login'));
        }
        exit();
    }

    //開始遍歴
    for ($i = count($path); $i >= 0; $i--) {
        //重組class路徑
        $class = '\panel\page';
        for ($x = 0; $x < $i; $x++) {
            $class .= '\\' . $path[$x];
        }
        $include_path = './..' . str_replace('\\', '/', $class) . '.php';

        //檢查存在
        if (file_exists($include_path)) {
            require_once($include_path);
            $up_path = array_slice($path, $i); //傳入在此之前的路徑

            /* create sql connect */
            $sqlcon = new mysqli(Cfg_Sql_Host, Cfg_Sql_dbUser, Cfg_Sql_dbPass, Cfg_Sql_dbName);
            if ($sqlcon->connect_errno) {
                http_response_code(500);
                echo json_encode(array('code' => 500, 'Message' => showText("Error_Page.something_happened")));
                exit();
            }
            $page = new $class($sqlcon, $up_path); //create class

            //檢查權限
            $access = $page->access($auth->islogin, $auth->userdata['Role'] ?? 0);
            if ($access == 200) {
                //頁面輸出
                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    $data = json_decode(file_get_contents("php://input"), true);

                    //無法解釋json
                    if ($data == null) {
                        http_response_code(500);
                        echo json_encode(array(
                            'code' => 500,
                            'Message' => showText('Error')
                        ));
                        exit();
                    }

                    echo json_encode($page->post($data));
                } else {
                    echo json_encode(array(
                        'title' => $page->get_Title(),
                        'head' => $page->get_Head(),
                        'path' => $page->path(),
                        'content' => $page->showPage()
                    ));
                }
            } else if ($access == 403) {
                //沒有權限
                http_response_code(403);
                echo json_encode(array('code' => 403, 'Message' => showText("Error_Page.Dont_Come")));
            } else if ($access == 401) {
                //需要登入
                http_response_code(401);
                echo json_encode(array('code' => 401, 'path' => './login'));
            }
            exit(); //存在即停止
        }
    }
    //不存在
    http_response_code(404);
    echo json_encode(array('code' => 404, 'Message' => showText("Error_Page.Where_you_go")));
    exit();
}
?>

    <!-- page container area start -->
    <div class="page-container">

        <!-- sidebar menu area start -->
        <div class="sidebar-menu">

            <!-- sidebar header -->
            <div class="sidebar-header">
                <div class="logo">
                    <a href=".."><img src="/panel/assets/images/icon/logo.png" alt="logo"></a>
                </div>
            </div>

            <!-- sidebar body -->
            <div class="main-menu">
                <div class="menu-inner">
                    <nav>
                        <ul class="metismenu" id="menu">

                            <!-- sidebar content -->
                            <li>
                                <a href="javascript:void(0)" aria-expanded="false" class="has-arrow">
                                    <i class="ti-dashboard"></i><span><?php echo showText("index.Console") ?></span>
                                </a>
                                <ul class="mm-collapse">
                                    <li>
                                        <a href="/panel/">
                                            <i class='fa fa-home'></i><span><?php echo showText("index.home") ?></span>
                                        </a>
                                    </li>
                                </ul>
                            </li>

                            <?php
                            /* NOTE: ⚠ 限制頁面 ⚠ */
                            /*if ($auth->userdata['Role'] == '2') {
                                echo '<li>
                                    <a href="javascript:void(0)" aria-expanded="false"><i class="fa fa-wrench"></i><span>管理後台</span></a>
                                    <ul class="collapse">
                                        <li><a href="/panel/admin_background/broadcast/" data-ajax="GET"><i class="ti-announcement"></i><span>廣播</span></a></li>
                                        <li><a href="/panel/admin_background/notify/" data-ajax="GET"><i class="ti-bell"></i><span>通知</span></a></li>
                                    </ul>
                                    </li>';
                            }*/
                            ?>

                        </ul>
                    </nav>
                </div>
            </div>

        </div>
        <!-- sidebar menu area end -->

        <!-- main content area start -->
        <div class="main-content">

            <div class="sticky-top">
                <!-- header area start -->
                <div class="header-area">
                    <div class="row align-items-center">

                        <!-- left content-->
                        <div class="col-md-6 col-sm-8 clearfix">
                            <div class="nav-btn fa-pull-left">
                                <span></span>
                                <span></span>
                                <span></span>
                            </div>
                        </div>

                        <!-- right content-->
                        <div class="col-md-6 col-sm-4 clearfix">
                            <ul class="notification-area fa-pull-right">

                                <!-- notify -->
                                <li class="dropdown">
                                    <i class="ti-bell dropdown-toggle" data-bs-toggle="dropdown" id="notify-bell"></i>
                                    <div class="dropdown-menu bell-notify-box notify-box">
                                        <span class="notify-title">通知</span>
                                        <div class="nofity-list" id="notify">
                                            <!-- notify-item -->
                                        </div>
                                    </div>
                                </li>

                            </ul>
                        </div>
                    </div>
                </div>
                <!-- header area end -->

                <!-- page title area start -->
                <div class="page-title-area">
                    <div class="row align-items-center">
                        <div class="col-sm-6">
                            <div class="breadcrumbs-area clearfix">

                                <!-- title content -->
                                <h4 class="page-title fa-pull-left" id="title"></h4>
                                <ul class="breadcrumbs fa-pull-left" id="path">
                                </ul>

                            </div>
                        </div>
                        <div class="col-sm-6 clearfix">
                            <div class="user-profile fa-pull-right">

                                <!-- user avatar -->
                                <img class="avatar user-thumb"
                                     src="https://www.gravatar.com/avatar/<?php echo md5(strtolower(trim($auth->userdata['Email']))); ?>"
                                     alt="avatar">
                                <h4 class="user-name dropdown-toggle" data-bs-toggle="dropdown">
                                    <span id="username"><?php echo $auth->userdata['Name'] ?? showText('index.visitor') ?></span><i class="fa fa-angle-down"></i>
                                </h4>
                                <div class="dropdown-menu">

                                    <!-- dropdown menu content -->
                                    <a class="dropdown-item" href="/panel/ChangeSetting" data-ajax="GET">
                                        <i class="ti-settings pr--10"></i><?php echo showText("ChangeSetting.setting") ?>
                                    </a>
                                    <?php
                                    if ($auth->islogin) {
                                        echo "<a class='dropdown-item g_id_signout' href='https://{$_SERVER['SERVER_NAME']}/panel/login?logout=1'>
                                                    <i class='fa fa-sign-out pr--10'></i>" . showText('index.Logout') .
                                            "</a>";
                                    } else {
                                        echo "<a class='dropdown-item' href='https://{$_SERVER['SERVER_NAME']}/panel/login'>
                                                    <i class='fa fa-sign-in pr--10'></i>" . showText('index.Login') .
                                            "</a>";
                                    }
                                    ?>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- page title area end -->

            <!-- Broadcast -->
            <!--<div class="alert-dismiss" id="Broadcast">
            <?php
            /*          $Broadcast_dismiss = $_COOKIE['Broadcast-dismiss'] ?? '0,0';
                        $stmt = $auth->sqlcon->prepare('SELECT ID, Msg, status, Always_close FROM Broadcast WHERE Broadcast = TRUE ORDER BY Time');
                        if (!$stmt->execute()) {
                            echo "<div class='alert alert-info alert-dismissible fade show' role='alert'>
                                Database Error!
                                <button type='button' class='close' data-dismiss='alert' aria-label='Close'><span class=\"fa fa-times\"></span></button>
                              </div>";
                        }
                        $result = $stmt->get_result();
                        while ($row = $result->fetch_assoc()) {
                            if (strpos($Broadcast_dismiss, strval($row['ID'])) === false) {
                                switch ($row['status']) {
                                    case 1:
                                        $status = 'success';
                                        break;
                                    case 2:
                                        $status = 'danger';
                                        break;
                                    case 3:
                                        $status = 'warning';
                                        break;
                                    case 4:
                                        $status = 'info';
                                        break;
                                    default:
                                        $status = 'primary';
                                        break;
                                } //status

                                $bc = "<div class='alert alert-{$status} alert-dismissible fade show' role='alert'>{$row['Msg']}"; //Msg

                                //Always_close
                                if ($row['Always_close'] == true)
                                    $bc .= "<button type='button' class='close' data-dismiss='alert' aria-label='Close' data-row-id='{$row['ID']}'><span class='fa fa-times'></span></button>";

                                $bc .= "</div>";
                                echo $bc;
                            }
                        }
                        */
            ?>
            </div>-->

            <!-- Main area start -->
            <div class="main-content-inner">

                <!-- Main content-->
                <div class="row" id="content">

                    <!-- Loading Card-->
                    <div class='col-8 mt-4'>
                        <div class="row gy-4 gx-0 m-0">
                            <div class='col-12'>
                                <div class="card">
                                    <div class='card-body'>
                                        <div class='placeholder-glow'>
                                            <h5 class="card-title">
                                                <span class="placeholder col-6"></span>
                                            </h5>
                                            <p class="card-text row m-0">
                                                <span class="placeholder col-2" style="margin-right: 1.5rem"></span>
                                                <span class="placeholder col" style="margin-right: 1.5rem"></span>
                                                <span class="placeholder col-2"></span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class='col-12'>
                                <div class="card ratio ratio-21x9 overflow-hidden placeholder-glow">
                                    <div class="card-body placeholder"></div>
                                </div>
                            </div>
                            <div class='col-12'>
                                <div class="card ">
                                    <div class='card-body'>
                                        <div class='placeholder-glow'>
                                            <h5 class="card-title">
                                                <span class="placeholder col-6"></span>
                                            </h5>
                                            <p class="card-text">
                                                <span class="placeholder col-6"></span>
                                                <span class="placeholder col-4"></span>
                                                <span class="placeholder col-8"></span>
                                                <span class="placeholder col-4"></span>
                                                <span class="placeholder col-7"></span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class='col-4 mt-4'>
                        <div class="row gy-4 gx-0 m-0">
                            <div class='col-12'>
                                <div class="card">
                                    <div class='card-body'>
                                        <div class='col-12 placeholder-glow row justify-content-center'>
                                            <h2 class="card-title text-center">
                                                <span class="placeholder col-8"></span>
                                            </h2>
                                            <a href="#" tabindex="-1" class="btn btn-rounded btn-primary disabled placeholder col-6"></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class='col-12'>
                                <div class="card">
                                    <div class='card-body'>
                                        <div class='placeholder-glow'>
                                            <p class="card-text">
                                                <span class="placeholder col-12 placeholder-lg"></span>
                                                <span class="placeholder col-3 placeholder-sm"></span>
                                            </p>
                                            <p class="card-text">
                                                <span class="placeholder col-12 placeholder-lg"></span>
                                                <span class="placeholder col-3 placeholder-sm"></span>
                                            </p>
                                            <p class="card-text">
                                                <span class="placeholder col-12 placeholder-lg"></span>
                                                <span class="placeholder col-3 placeholder-sm"></span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- language translate -->
                <pre style="display: none" id="globalLang">
                    <?php
                    echo json_encode(array(
                        'Error' => showText('Error')
                    ))
                    ?>
                </pre>

            </div>
        </div>
        <!-- Main area end -->

    </div>
    <!-- main content area end -->

<?php require_once('./stable/footer.php');
