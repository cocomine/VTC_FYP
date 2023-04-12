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

use cocomine\LoadPageFactory;
use cocomine\MyAuth;
use cocomine\MyAuthException;
use page\home;

/* 取得路徑 */
static $path;
$path = fetch_path();

/* header */
require_once('./stable/header.php');

//start auth
static $auth;
$auth = new MyAuth(Cfg_Sql_Host, Cfg_Sql_dbName, Cfg_Sql_dbUser, Cfg_Sql_dbPass, Cfg_Cookies_Path); //startup
try {
    $auth->checkAuth();
} catch (MyAuthException $e) {
    ob_clean();
    http_response_code(500);
    require(Cfg_500_Error_File_Path);
    exit();
}

/* API互動介面 (即係唔係俾人睇) */
if ($path[0] == "api") {
    run_apis($path, $auth);
    exit();
}

/* AJAX內容 */
$_SERVER['HTTP_X_REQUESTED_WITH'] = strtolower(@$_SERVER['HTTP_X_REQUESTED_WITH']);
if ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'xmlhttprequest') {
    run_page($path, $auth);
    exit();
}

/**
 * 取得前往頁面的title
 * @param array $path
 * @return array|null
 */
function run_og(array $path): ?array{
    $access = 404;

    //輸出頁面 home 頁面
    if (count($path) < 1) {
        require_once('./page/home.php');

        //建立頁面
        try {
            $page = LoadPageFactory::createPage('page\\home', __DIR__ . '/', array());
            $access = $page->access(true, 0, false);

            if($access === 200){
                return array(
                    'title' => $page->get_Title(),
                    'description' => $page->get_description() ?? null,
                    'image' => $page->get_image() ?? null
                );
            }
        } catch (Exception $e) {}
    } else {
        /* 頁面搜尋 */
        for ($i = count($path); $i >= 0; $i--) {
            //重組class路徑
            $class = 'page';
            for ($x = 0; $x < $i; $x++) $class .= '\\' . $path[$x];
            $up_path = array_slice($path, $i); //傳入在此之前的路徑
            $up_path = array_sanitize($up_path); //消毒

            //建立頁面
            try {
                $page = LoadPageFactory::createPage($class, __DIR__ . '/', $up_path);
                $access = $page->access(true, 0, false);

                if($access === 200){
                    return array(
                        'title' => $page->get_Title(),
                        'description' => $page->get_description() ?? null,
                        'image' => $page->get_image() ?? null
                    );
                }
            } catch (Exception $e) {
                continue; //如不存在跳過
            }
        }
        return array(
            'title' => showText('Error_Page.404_title'),
            'description' => showText('Error_Page.Where_you_go'),
            'image' => null
        );
    }
    return array(
        'title' => showText('Error_Page.500_title'),
        'description' => showText('Error_Page.something_happened'),
        'image' => null
    );
}

/**
 * 取得路徑
 * @return array 路徑
 */
function fetch_path(): array {
    // 消毒/分割
    $path = strtolower(filter_var(trim($_SERVER['REQUEST_URI']), FILTER_SANITIZE_STRING));
    $path = explode("/", $path);

    //清除多餘數組
    if ($path[count($path) - 1] === "") {
        $path = array_slice($path, 0, -1);
    }
    if (preg_match("/^\?.*$/", $path[count($path) - 1])) {
        $path = array_slice($path, 0, -1);
    }
    if ($path[0] === "") {
        $path = array_slice($path, 1);
    }
    if (preg_match("/^(.+)(\?.*)$/", $path[count($path) - 1], $matches)) {
        $path[count($path) - 1] = $matches[1];
    }

    return $path;
}

/**
 * 展示頁面
 * @param array $path 路徑
 * @param MyAuth $auth MyAuth class
 * @return void
 */
function run_page(array $path, MyAuth $auth) {
    ob_clean();
    header("content-type: text/json; charset=utf-8");
    $access = 404; //錯誤代碼

    //輸出頁面 home 頁面
    if (count($path) < 1) {
        require_once('./page/home.php');
        $homePage = new home($auth->sqlcon);

        //檢查權限
        $access = $homePage->access($auth->islogin, $auth->userdata['Role'] ?? 0, $_SERVER['REQUEST_METHOD'] == 'POST');
        if ($access == 200) {  //正常訪問
            //頁面輸出
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $data = json_decode(file_get_contents("php://input"), true);

                //無法解釋json
                if ($data === null) $access = 500;
                else {
                    $data = array_sanitize($data);
                    echo json_encode($homePage->post($data));
                }
            } else {
                echo json_encode(array(
                    'title' => $homePage->get_Title(),
                    'head' => $homePage->get_Head(),
                    'path' => $homePage->path(),
                    'content' => $homePage->showPage()
                ));
            }
        }
    } else {

        /* 頁面搜尋 */
        for ($i = count($path); $i >= 0; $i--) {
            //重組class路徑
            $class = 'page';
            for ($x = 0; $x < $i; $x++) $class .= '\\' . $path[$x];
            $up_path = array_slice($path, $i); //傳入在此之前的路徑
            $up_path = array_sanitize($up_path); //消毒

            //建立頁面
            try {
                $page = LoadPageFactory::createPage($class, __DIR__ . '/', $up_path);
            } catch (Exception $e) {
                continue; //如不存在跳過
            }

            //檢查權限
            $access = $page->access($auth->islogin, $auth->userdata['Role'] ?? 0, $_SERVER['REQUEST_METHOD'] == 'POST');
            if ($access == 200) {  //正常訪問
                //頁面輸出
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $data = json_decode(file_get_contents("php://input"), true);

                    //無法解釋json
                    if ($data === null) $access = 500;
                    else {
                        $data = array_sanitize($data);
                        echo json_encode($page->post($data));
                    }
                } else {
                    echo json_encode(array(
                        'title' => $page->get_Title(),
                        'head' => $page->get_Head(),
                        'path' => $page->path(),
                        'content' => $page->showPage()
                    ));
                }
            }
            break;
        }
    }
    echo_error($access);
}

/**
 * API互動介面 (即係唔係俾人睇)
 * @param array $path 路徑
 * @param MyAuth $auth MyAuth class
 * @return void
 */
function run_apis(array $path, MyAuth $auth) {
    ob_clean();
    $access = 404; //錯誤代碼

    if (count($path) >= 2) {
        //開始遍歴
        for ($i = count($path); $i >= 1; $i--) {
            //重組class路徑
            $class = 'apis';
            for ($x = 1; $x < $i; $x++) $class .= '\\' . $path[$x];
            $up_path = array_slice($path, $i); //傳入在此之前的路徑
            $up_path = array_sanitize($up_path); //消毒

            //建立頁面
            try {
                $api = LoadPageFactory::createApi($class, __DIR__ . '/', $up_path);
            } catch (Exception $e) {
                continue; //如不存在跳過
            }

            //檢查權限
            $access = $api->access($auth->islogin, $auth->userdata['Role'] ?? 0);
            if ($access == 200) {  //正常訪問

                if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                    /* Get 請求 */
                    $api->get();
                } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
                    /* Delete 請求 */
                    if (preg_match('/((text|application)\/json).*/', $_SERVER['CONTENT_TYPE'])) {
                        /* json type content */
                        $data = json_decode(file_get_contents("php://input"), true);

                        if ($data === null) {
                            echo_error(500); //無法解釋json
                        } else {
                            $data = array_sanitize($data);
                            $api->delete($data);
                        }
                    } else {
                        $api->delete(null);
                    }
                } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    /* Post 請求 */
                    if (preg_match('/((text|application)\/json).*/', $_SERVER['CONTENT_TYPE'])) {
                        /* json type content */
                        $data = json_decode(file_get_contents("php://input"), true);

                        if ($data === null) {
                            echo_error(500); //無法解釋json
                        } else {
                            $data = array_sanitize($data);
                            $api->post($data);
                        }
                    } else {
                        $api->post(null);
                    }
                } elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
                    /* Put 請求 */
                    if (preg_match('/((text|application)\/json).*/', $_SERVER['CONTENT_TYPE'])) {
                        /* json type content */
                        $data = json_decode(file_get_contents("php://input"), true);

                        if ($data === null) {
                            echo_error(500); //無法解釋json
                        } else {
                            $data = array_sanitize($data);
                            $api->put($data);
                        }
                    } else {
                        $api->put(null);
                    }
                } else {
                    echo_error(405); //不符合任何請求
                }
                break;
            }
        }
    }
    echo_error($access);
}

?>

    <!-- main wrapper start -->
    <div class="horizontal-main-wrapper">
        <!-- main header area start -->
        <div class="col-12 col-sm py-1 fixed-top bg-light top-0" style="display: none" id="fixed-header">
            <div class="row justify-content-between align-items-center">
                <div class="col-auto">
                    <a href="/"><img src="/assets/images/icon/logo.png" alt="logo" style="max-width: 150px"></a>
                </div>

                <!-- notify START -->
                <div class="col-auto">
                    <div class="d-md-inline-block d-block me-md-4">
                        <ul class="notification-area">
                            <!-- notify START -->
                            <li class="dropdown">
                                <i class="ti-bell dropdown-toggle" data-bs-toggle="dropdown" id="notify-bell"></i>
                                <div class="dropdown-menu bell-notify-box notify-box">
                                    <span class="notify-title"><?php echo showText('notify.Content.Notify') ?></span>
                                    <div class="nofity-list scrollbar-dynamic" data-notify>
                                        <!-- notify-item -->
                                    </div>
                                </div>
                            </li>
                            <!-- notify END -->
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="sticky-sm-top">
            <div class="mainheader-area">
                <div class="container">
                    <div class="row align-items-center">
                        <div class="col-12 col-sm mt-2">
                            <div class="row justify-content-between align-items-center">
                                <div class="col-auto">
                                    <a href="/"><img src="/assets/images/icon/logo.png" alt="logo" style="max-width: 150px"></a>
                                </div>

                                <!-- notify START -->
                                <div class="col-auto">
                                    <div class="d-md-inline-block d-block me-md-4">
                                        <ul class="notification-area">
                                            <!-- notify START -->
                                            <li class="dropdown">
                                                <i class="ti-bell dropdown-toggle" data-bs-toggle="dropdown" data-notify-bell></i>
                                                <div class="dropdown-menu bell-notify-box notify-box">
                                                    <span class="notify-title"><?php echo showText('notify.Content.Notify') ?></span>
                                                    <div class="nofity-list scrollbar-dynamic" data-notify>
                                                        <!-- notify-item -->
                                                    </div>
                                                </div>
                                            </li>
                                            <!-- notify END -->
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- profile info  -->
                        <div class="col-12 col-sm-auto clearfix text-end">
                            <div class="clearfix d-md-inline-block d-block">
                                <div class="user-profile">
                                    <!-- user avatar -->
                                    <img class="avatar user-thumb"
                                         src="https://www.gravatar.com/avatar/<?php echo md5(strtolower(trim($auth->userdata['Email']))); ?>"
                                         alt="avatar">
                                    <h4 class="user-name dropdown-toggle" data-bs-toggle="dropdown">
                                        <span id="username"><?php echo $auth->userdata['Name'] ?? showText('index.visitor') ?></span><i class="fa fa-angle-down"></i>
                                    </h4>
                                    <div class="dropdown-menu" style="z-index: 1030">
                                        <!-- dropdown menu content START -->
                                        <a class="dropdown-item" href="https://<?php echo $_SERVER['SERVER_NAME'] ?>/panel/ChangeSetting" target="_blank">
                                            <i class="ti-settings pr--10"></i><?php echo showText("ChangeSetting.setting") ?>
                                        </a>
                                        <?php
                                        if ($auth->islogin) {
                                            echo "<a class='dropdown-item g_id_signout' href='/reserve_view'>
                                                    <i class='fa-solid fa-book-bookmark pr--10'></i>預訂管理
                                                </a>
                                                <a class='dropdown-item g_id_signout' href='https://{$_SERVER['SERVER_NAME']}/panel/login?logout=1'>
                                                   <i class='fa fa-sign-out pr--10'></i>" . showText('index.Logout') .
                                                "</a>";
                                        } else {
                                            echo "<a class='dropdown-item' href='https://{$_SERVER['SERVER_NAME']}/panel/login'>
                                                    <i class='fa fa-sign-in pr--10'></i>" . showText('index.Login') .
                                                "</a>";
                                        }
                                        ?>
                                        <!-- dropdown menu content END -->
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- main header area end -->
        <!-- header area start -->
        <div class="header-area header-bottom">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-9  d-none d-lg-block">
                        <div class="horizontal-menu">
                            <nav>
                                <ul id="nav_menu">

                                    <!-- sidebar content; 由於以提供自定方式, 注意不會再使用bootstrap導航欄-->
                                    <li>
                                        <a href="/">
                                            <i class="fa fa-home"></i><span><?php echo showText("index.home") ?></span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0)" role="button">玩樂體驗</a>
                                        <ul class="submenu">
                                            <li><a href="/water">水上活動</a></li>
                                            <li><a href="/land">陸上活動</a></li>
                                            <li><a href="/air">空中活動</a></li>
                                        </ul>
                                    </li>
                                    <li><a href="/xmap">X-map</a></li>


                                    <?php /* 導航 */
                                    if ($auth->userdata['Role'] >= 1) {
                                        echo '';
                                    }
                                    if ($auth->userdata['Role'] >= 2) {
                                        echo '';
                                    }
                                    if ($auth->userdata['Role'] >= 3) {
                                        echo '';
                                    }
                                    ?>
                                    <!-- sidebar content End-->

                                </ul>
                            </nav>
                        </div>
                    </div>
                    <!-- nav and search button -->
                    <div class="col-lg-3 clearfix">
                        <div class="search-box">
                            <form method="GET" action="/search">
                                <input type="text" name="search" placeholder="Search..." required value="<?php echo $_GET['search']?>">
                                <i class="ti-search"></i>
                            </form>
                        </div>
                    </div>
                    <!-- mobile_menu -->
                    <div class="col-12 d-block d-lg-none">
                        <div id="mobile_menu"></div>
                    </div>
                </div>
            </div>
        </div>
        <!-- header area end -->
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
        <!-- Broadcast End -->

        <!-- Main area start -->
        <div class="main-content-inner main-content">

            <!-- global language translate -->
            <pre style="display: none" id="globalLang">
                <?php
                echo json_encode(array(
                    'Error' => showText('Error'),
                    'notify' => showText('notify.Content.Time')
                ))
                ?>
            </pre>
            <!-- global language translate End -->

            <div class="heard-area pt-4 pb-3">
                <div class="container">
                    <h4 class="page-title" id="title"></h4>
                    <nav class="pt-2" aria-label="breadcrumb">
                        <ol class="breadcrumb" id="path"></ol>
                    </nav>
                </div>
            </div>

            <!-- Main content-->
            <div id="content" class="overflow-hidden"></div>
        </div>
        <!-- Main area end -->
    </div>
    <!-- main content area end -->

<?php require_once('./stable/footer.php');
