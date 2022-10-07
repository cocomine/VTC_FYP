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

use cocomine\API\notifyAPI;
use cocopixelmc\Auth\MyAuth;
use panel\page\home;

/* header */
const title = "index.title";
require_once('./stable/header.php');

static $auth;
$auth = new MyAuth(Cfg_Sql_Host, Cfg_Sql_dbName, Cfg_Sql_dbUser, Cfg_Sql_dbPass, Cfg_500_Error_File_Path, Cfg_Cookies_Path); //startup
$auth->checkAuth(); //start auth

//check auth
if (!$auth->islogin) {
    ob_clean();
    if (isset($_SERVER['HTTP_AJAX'])) {
        header("content-type: text/json; charset=utf-8");
        $out = array('return' => '/panel/login');
        ob_flush();
        echo json_encode($out);
    } else {
        header("Location: /panel/login");
    }
    exit();
}

/* API互動介面 (即係唔係俾人睇) */
if (isset($_GET['api'])) {
    switch ($_GET['api']) {
        case 'notifyAPI':
            /* 通知API */
            require_once('./function/notifyAPI.php');
            $notifyAPI = new notifyAPI($auth->sqlcon);
            $output = $notifyAPI->Show_notify($_SESSION['UUID'], 20);
            break;
        default:
            $output = array(
                'error' => 'Request not include request API'
            );
            break;
    }
}

/* AJAX內容 */
$_SERVER['HTTP_X_REQUESTED_WITH'] = strtolower(@$_SERVER['HTTP_X_REQUESTED_WITH']);
if ($_SERVER['HTTP_X_REQUESTED_WITH'] === 'xmlhttprequest' && !isset($_GET['api'])) {
    //消毒/分割
    $path = strtolower(filter_var(trim(@$_GET['p']), FILTER_SANITIZE_STRING));
    $path = explode("/", $path);
    if($path[count($path)-1] === ""){
        $path = array_slice($path, 0, -1); //清除多餘數組
    }

    //開始遍歴
    if (count($path) < 1) {
        // home 頁面
        include ('./page/home.php');
        $page = new home($auth->sqlcon);
        $output = array(
            'title' => showText("index.title"),
            'head' => showText("index.home"),
            'path' => $page->path(),
            'content' => $page->showPage()
        );
    } else {
        for ($i = count($path); $i >= 0; $i--) {

            //重組路徑
            $class = '\panel\page';
            for ($x = 0; $x < $i; $x++) {
                $class .= '\\' . $path[$x];
            }
            $include_path = './..' . str_replace('\\', '/', $class) . '.php';

            if (file_exists($include_path)) { //檢查存在
                require($include_path);
                $up_path = array_slice($path, $i); //傳入在此之前的路徑
                $page = new $class($auth->sqlcon, $up_path);

                if($page->is_access($auth->userdata['Role'])){ //檢查權限
                    //頁面輸出
                    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                        $output = $page->post();
                    } else {
                        $output = array(
                            'title' => $page->get_Title(),
                            'head' => $page->get_Head(),
                            'path' => $page->path(),
                            'content' => $page->showPage()
                        );
                    }
                }else{
                    //沒有權限
                    $output = array('return' => '/panel');
                }
                break; //存在即停止迴圈
            } else if ($i <= 0) {
                //不存在
                $output = array('return' => '/panel');
            }
        }
    }

}

/* AJAX內容OUTPUT */
if ($_SERVER['HTTP_X_REQUESTED_WITH'] === 'xmlhttprequest') {
    ob_clean();
    header("Content-Type:text/json; charset=utf-8");
    ob_flush();
    if (isset($output) && !empty($output)) {
        echo json_encode($output);
    } else {
        echo "{'error': 'No content'}";
    }
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
                            <a href="javascript:void(0)" aria-expanded="false">
                                <i class="ti-dashboard"></i><span><?php echo showText("index.Console") ?></span>
                            </a>
                            <ul class="collapse">
                                <li>
                                    <a href="/panel/" data-ajax="GET">
                                        <i class='fa fa-home'></i><span><?php echo showText("index.home") ?></span>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <?php
                        /* NOTE: ⚠ 限制頁面 ⚠ */
                        if ($auth->userdata['Role'] == '2') {
                            echo '<li>
                                <a href="javascript:void(0)" aria-expanded="false"><i class="fa fa-wrench"></i><span>管理後台</span></a>
                                <ul class="collapse">
                                    <li><a href="/panel/admin_background/broadcast/" data-ajax="GET"><i class="ti-announcement"></i><span>廣播</span></a></li>
                                    <li><a href="/panel/admin_background/notify/" data-ajax="GET"><i class="ti-bell"></i><span>通知</span></a></li>
                                </ul>
                                </li>';
                        }
                        ?>

                    </ul>
                </nav>
            </div>
        </div>

    </div>
    <!-- sidebar menu area end -->

    <!-- main content area start -->
    <div class="main-content">

        <!-- header area start -->
        <div class="header-area">
            <div class="row align-items-center">

                <!-- left content-->
                <div class="col-md-6 col-sm-8 clearfix">
                    <div class="nav-btn pull-left">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                </div>

                <!-- right content-->
                <div class="col-md-6 col-sm-4 clearfix">
                    <ul class="notification-area pull-right">

                        <!-- contnt -->
                        <li class="dropdown">
                            <i class="ti-bell dropdown-toggle" data-toggle="dropdown" id="notify-bell"></i>
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
                        <h4 class="page-title pull-left" id="title"></h4>
                        <ul class="breadcrumbs pull-left" id="path">
                        </ul>

                    </div>
                </div>
                <div class="col-sm-6 clearfix">
                    <div class="user-profile pull-right">

                        <!-- user avatar -->
                        <img class="avatar user-thumb"
                             src="https://www.gravatar.com/avatar/<?php echo md5(strtolower(trim($auth->userdata['Email']))); ?>"
                             alt="avatar">
                        <h4 class="user-name dropdown-toggle"
                            data-toggle="dropdown"><?php echo $auth->userdata['Name'] ?><i
                                    class="fa fa-angle-down"></i></h4>
                        <div class="dropdown-menu">

                            <!-- dropdown menu content -->
                            <a class="dropdown-item" href="/panel/ChangeSetting" data-ajax="GET"><i
                                        class="ti-settings"></i>&nbsp;&nbsp;&nbsp;<?php echo showText("ChangeSetting.setting") ?>
                            </a>
                            <a class="dropdown-item" href="https://<?php echo $_SERVER['SERVER_NAME']?>/panel/login?logout=1"><i class="fa fa-sign-out"></i>&nbsp;&nbsp;&nbsp;<?php echo showText("index.Logout") ?>
                            </a>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        <!-- page title area end -->

        <!-- Broadcast -->
        <div class="alert-dismiss" id="Broadcast">
            <?php
            $Broadcast_dismiss = $_COOKIE['Broadcast-dismiss'] ?? '0,0';
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
            ?>
        </div>

        <!-- Main area start -->
        <div class="main-content-inner">

            <!-- Main content-->
            <div class="card-area">
                <div class="row" id="content">

                    <!-- Loading Card-->
                    <div class='col-12 mt-4'>
                        <div class='card'>
                            <div class='card-body'>
                                <div class='col-12 mt-4'>
                                    <div class='card'>
                                        <div class='card-body'>
                                            <div class='loading-card'>
                                                <div class='lc-heard'></div>
                                                <div class='lc-long'></div>
                                                <div class='lc-boll'></div>
                                                <div class='lc-short'></div>
                                                <div class='lc-blank'></div>
                                                <div class='lc-body'></div>
                                                <div class='lc-big'></div>
                                                <div class='lc-button'></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <!-- Main area end -->

    </div>
    <!-- main content area end -->

<?php require_once('./stable/footer.php');
