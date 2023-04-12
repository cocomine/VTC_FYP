<?php
/**
 * Copyright (c) 2020.
 * Create by cocomine
 */

namespace page;

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

        $hkActivities = '';
        $stmt = $this->sqlcon->prepare("SELECT ID, review, state, name, summary, country, thumbnail, create_time FROM Event WHERE review = 1 AND state = 1 AND country = 'HK' ORDER BY create_time DESC LIMIT 5");
        if (!$stmt->execute()) {
            echo_error(500);
            exit();
        }

        $rs = $stmt->get_result();
        while($row = $rs->fetch_assoc()) {
            $hkActivities .= "<div class='item'><div class='card card-block mx-2' style='min-width: 300px;'><div class='ratio ratio-4x3 card-img-top overflow-hidden'>";
            $hkActivities .= "<img class='owl-lazy' data-src='/panel/api/media/".$row['thumbnail']."' alt='".$row['thumbnail']."'></div><div class='card-body'>";
            $hkActivities .= "<h5 class='card-title'>".$row['name']."</h5>";
            $hkActivities .= "<p class='card-text'>".$row['summary']."</p>";


            $hkActivities .= "<a href='/details/".$row['ID']."' class='btn btn-primary stretched-link btn-rounded'>了解更多</a></div></div></div>";
        }

        $cnActivities = '';
        $stmt->prepare("SELECT ID, review, state, name, summary, country, thumbnail, create_time FROM Event WHERE review = 1 AND state = 1 AND country = 'CN' ORDER BY create_time DESC LIMIT 5");
        if (!$stmt->execute()) {
            echo_error(500);
            exit();
        }

        $rs = $stmt->get_result();
        while($row = $rs->fetch_assoc()) {
            $cnActivities .= "<div class='item'><div class='card card-block mx-2' style='min-width: 300px;'><div class='ratio ratio-4x3 card-img-top overflow-hidden'>";
            $cnActivities .= "<img class='owl-lazy' data-src='/panel/api/media/".$row['thumbnail']."' alt='".$row['thumbnail']."'></div><div class='card-body'>";
            $cnActivities .= "<h5 class='card-title'>".$row['name']."</h5>";
            $cnActivities .= "<p class='card-text'>".$row['summary']."</p>";
            $cnActivities .= "<a href='/details/".$row['ID']."' class='btn btn-primary stretched-link btn-rounded'>了解更多</a></div></div></div>";
        }

        $moActivities = '';
        $stmt->prepare("SELECT ID, review, state, name, summary, country, thumbnail, create_time FROM Event WHERE review = 1 AND state = 1 AND country = 'MO' ORDER BY create_time DESC LIMIT 5");
        if (!$stmt->execute()) {
            echo_error(500);
            exit();
        }

        $rs = $stmt->get_result();
        while($row = $rs->fetch_assoc()) {
            $moActivities .= "<div class='item'><div class='card card-block mx-2' style='min-width: 300px;'><div class='ratio ratio-4x3 card-img-top overflow-hidden'>";
            $moActivities .= "<img class='owl-lazy' data-src='/panel/api/media/".$row['thumbnail']."' alt='".$row['thumbnail']."'></div><div class='card-body'>";
            $moActivities .= "<h5 class='card-title'>".$row['name']."</h5>";
            $moActivities .= "<p class='card-text'>".$row['summary']."</p>";
            $moActivities .= "<a href='/details/".$row['ID']."' class='btn btn-primary stretched-link btn-rounded'>了解更多</a></div></div></div>";
        }

        $twActivities = '';
        $stmt->prepare("SELECT ID, review, state, name, summary, country, thumbnail, create_time FROM Event WHERE review = 1 AND state = 1 AND country = 'TW' ORDER BY create_time DESC LIMIT 5");
        if (!$stmt->execute()) {
            echo_error(500);
            exit();
        }

        $rs = $stmt->get_result();
        while($row = $rs->fetch_assoc()) {
            $twActivities .= "<div class='item'><div class='card card-block mx-2' style='min-width: 300px;'><div class='ratio ratio-4x3 card-img-top overflow-hidden'>";
            $twActivities .= "<img class='owl-lazy' data-src='/panel/api/media/".$row['thumbnail']."' alt='".$row['thumbnail']."'></div><div class='card-body'>";
            $twActivities .= "<h5 class='card-title'>".$row['name']."</h5>";
            $twActivities .= "<p class='card-text'>".$row['summary']."</p>";
            $twActivities .= "<a href='/details/".$row['ID']."' class='btn btn-primary stretched-link btn-rounded'>了解更多</a></div></div></div>";
        }
        return <<<body
<link rel="stylesheet" href="/assets/css/myself/page/home.css">
<div id='homeBackground' class="position-relative">
    <div class="row justify-content-center align-items-center">
        <div class="col-auto">
            <h5>體驗刺激，享受不一樣的生活點滴</h5>
            
            <nav class="navbar navbar-expand-lg bg-body-tertiary">
              <div class="container-fluid">
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                  <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                      <div class="btn-group dropend">
                        <button type="button" class="btn btn-light btn-lg btn-rounded me-2" id="allNew">
                          <i class="fa-solid fa-down-left-and-up-right-to-center">&nbsp所有最新</i>
                        </button>
                      </div>
                    </li>
                    <li class="nav-item">
                      <div class="btn-group dropend">
                            <button type="button" class="btn btn-light dropdown-toggle btn-lg btn-rounded me-2" data-bs-toggle="dropdown" aria-expanded="false">
                              <i class="fa-solid fa-fire">&nbsp香港熱門</i>
                            </button>
                            <ul class="dropdown-menu">
                                <div class="hstack gap-3">
                                  <button type="button" class="btn btn-light btn-lg btn-rounded me-2" id="hotHkCanoeing">獨木舟</button>
                                  <button type="button" class="btn btn-light btn-lg btn-rounded me-2" id="hotHkClimbing">攀岩</button>
                                  <button type="button" class="btn btn-light btn-lg btn-rounded me-2" id="hotHkDiving">潛水</button>
                                  <button type="button" class="btn btn-light btn-lg btn-rounded me-2" id="hotHkParagliding">滑翔傘</button>
                                  <button type="button" class="btn btn-light btn-lg btn-rounded me-2" id="hotHkHiking">遠足</button>
                                </div>
                            </ul>
                        </div>
                    </li>
                    <li class="nav-item">
                      <div class="btn-group dropend">
                            <button type="button" class="btn btn-light dropdown-toggle btn-lg btn-rounded me-2" data-bs-toggle="dropdown" aria-expanded="false">
                              <i class="fa-solid fa-fire">&nbsp中國大陸熱門</i>
                            </button>
                            <ul class="dropdown-menu">
                              <div class="hstack gap-3">
                                <button type="button" class="btn btn-light btn-lg btn-rounded me-2" id="hotCnCanoeing">獨木舟</button>
                                <button type="button" class="btn btn-light btn-lg btn-rounded me-2" id="hotCnClimbing">攀岩</button>
                                <button type="button" class="btn btn-light btn-lg btn-rounded me-2" id="hotCnHotAirBalloon">熱氣球</button>
                                <button type="button" class="btn btn-light btn-lg btn-rounded me-2" id="hotCnMountaineering">登山</button>
                                <button type="button" class="btn btn-light btn-lg btn-rounded me-2" id="hotCnParagliding">滑翔傘</button>
                                <button type="button" class="btn btn-light btn-lg btn-rounded me-2" id="hotCnSkiing">滑雪</button>
                                <button type="button" class="btn btn-light btn-lg btn-rounded me-2" id="hotCnHiking">遠足</button>
                              </div>
                            </ul>
                        </div>
                    </li>
                    <li class="nav-item">
                      <div class="btn-group dropend">
                            <button type="button" class="btn btn-light dropdown-toggle btn-lg btn-rounded me-2" data-bs-toggle="dropdown" aria-expanded="false">
                              <i class="fa-solid fa-fire">&nbsp澳門熱門</i>
                            </button>
                            <ul class="dropdown-menu">
                              <div class="hstack gap-3">
                                <button type="button" class="btn btn-light btn-lg btn-rounded me-2" id="hotMoBungy">笨豬跳</button>
                                <button type="button" class="btn btn-light btn-lg btn-rounded me-2" id="hotMoClimbing">攀岩</button>
                              </div>
                            </ul>
                        </div>
                    </li>
                    <li class="nav-item">
                      <div class="btn-group dropend">
                            <button type="button" class="btn btn-light dropdown-toggle btn-lg btn-rounded me-2" data-bs-toggle="dropdown" aria-expanded="false">
                              <i class="fa-solid fa-fire">&nbsp台灣熱門</i>
                            </button>
                            <ul class="dropdown-menu">
                              <div class="hstack gap-3">
                                <button type="button" class="btn btn-light btn-lg btn-rounded me-2" id="hotTwCanoeing">獨木舟</button>
                                <button type="button" class="btn btn-light btn-lg btn-rounded me-2" id="hotTwClimbing">攀岩</button>
                                <button type="button" class="btn btn-light btn-lg btn-rounded me-2" id="hotTwDiving">潛水</button>
                                <button type="button" class="btn btn-light btn-lg btn-rounded me-2" id="hotTwMountaineering">登山</button>
                                <button type="button" class="btn btn-light btn-lg btn-rounded me-2" id="hotTwParachute">跳傘</button>
                                <button type="button" class="btn btn-light btn-lg btn-rounded me-2" id="hotTwParagliding">滑翔傘</button>
                                <button type="button" class="btn btn-light btn-lg btn-rounded me-2" id="hotTwHiking">遠足</button>   
                              </div>
                            </ul>
                      </div>
                    </li>
                  </ul>
                </div>
              </div>
            </nav>
        </div>
    </div>
</div>
body . <<<body
<div class="container mt-4" id="activitiesResult">
    <div class="row gy-4">
    
      <div class="col-12">
        <h3><b>香港地區最新活動</b></h3></br>
        <div class="owl-carousel owl-theme"><!-- 呢度要留意要加返 'owl-theme' class -->
          $hkActivities
        </div>
      </div>

      <div class="col-12">
        <h3><b>中國大陸地區最新活動</b></h3></br>
        <div class="owl-carousel owl-theme">
          $cnActivities
        </div>
      </div>
  
      <div class="col-12">
        <h3><b>澳門地區最新活動</b></h3></br>
        <div class="owl-carousel owl-theme">
          $moActivities
        </div>
      </div>

      <div class="col-12">
        <h3><b>台灣地區最新活動</b></h3></br>
        <div class="owl-carousel owl-theme">
          $twActivities
        </div>
      </div>
    </div>
</div>
body . <<<body
<script>
loadModules(['myself/page/home'])
</script>
body;
    }

    /* POST請求 */
    function post(array $data): array {
        global $auth;
        $activitiesSelection = $data['activitiesSelection'];


        if($activitiesSelection != 'allNew') {
            $output = [];
            $activityCountry = '';
            $activityType = '';

            /* 提供香港熱門 */
            /* 獨木舟 */
            if ($activitiesSelection == 'hotHkCanoeing') {
                $stmt = $this->sqlcon->prepare("SELECT ID, review, state, name, country, summary, thumbnail, create_time, type FROM Event WHERE review = 1 AND state = 1 AND type = 0 AND country = 'HK' AND tag LIKE '%獨木%' AND tag LIKE '%舟%'");
                if (!$stmt->execute()) {
                    return array(
                        'code' => 500,
                        'Title' => showText('Error_Page.something_happened'),
                        'Message' => $stmt->error,
                    );
                }

                $rs = $stmt->get_result();
                while ($row = $rs->fetch_assoc()) {
                    $stmt->prepare("SELECT ROUND(SUM(r.rate)/COUNT(*), 1) AS 'rate', COUNT(*) AS 'comments' FROM Book_review r, Book_event b WHERE r.Book_ID = b.ID AND event_ID = ?");
                    $stmt->bind_param("i", $row['ID']);
                    $stmt->execute();
                    $rate = $stmt->get_result()->fetch_assoc();

                    $output[] = array(
                        'id' => $row['ID'],
                        'title' => $row['name'],
                        'link' => $row['thumbnail'],
                        'summary' => $row['summary'],
                        'rate' => $rate['rate'],
                        'comments' => $rate['comments'],
                    );
                }
                $activityCountry = '香港';
                $activityType = '獨木舟';
            }

            /* 攀岩 */
            if ($activitiesSelection == 'hotHkClimbing') {
                $stmt = $this->sqlcon->prepare("SELECT ID, review, state, name, country, summary, thumbnail, create_time, type FROM Event WHERE review = 1 AND state = 1 AND type = 1 AND country = 'HK' AND tag LIKE '%岩%' AND tag LIKE '%攀岩%'");
                if (!$stmt->execute()) {
                    return array(
                        'code' => 500,
                        'Title' => showText('Error_Page.something_happened'),
                        'Message' => $stmt->error,
                    );
                }
                $rs = $stmt->get_result();
                while ($row = $rs->fetch_assoc()) {
                    $stmt->prepare("SELECT ROUND(SUM(r.rate)/COUNT(*), 1) AS 'rate', COUNT(*) AS 'comments' FROM Book_review r, Book_event b WHERE r.Book_ID = b.ID AND event_ID = ?");
                    $stmt->bind_param("i", $row['ID']);
                    $stmt->execute();
                    $rate = $stmt->get_result()->fetch_assoc();

                    $output[] = array(
                        'id' => $row['ID'],
                        'title' => $row['name'],
                        'link' => $row['thumbnail'],
                        'summary' => $row['summary'],
                        'rate' => $rate['rate'],
                        'comments' => $rate['comments'],
                    );
                }

                $activityCountry = '香港';
                $activityType = '攀岩';
            }

            /* 潛水 */
            if ($activitiesSelection == 'hotHkDiving') {
                $stmt = $this->sqlcon->prepare("SELECT ID, review, state, name, country, summary, thumbnail, create_time, type FROM Event WHERE review = 1 AND state = 1 AND type = 0 AND country = 'HK' AND tag LIKE '%潛%'");
                if (!$stmt->execute()) {
                    return array(
                        'code' => 500,
                        'Title' => showText('Error_Page.something_happened'),
                        'Message' => $stmt->error,
                    );
                }
                $rs = $stmt->get_result();
                while ($row = $rs->fetch_assoc()) {
                    $stmt->prepare("SELECT ROUND(SUM(r.rate)/COUNT(*), 1) AS 'rate', COUNT(*) AS 'comments' FROM Book_review r, Book_event b WHERE r.Book_ID = b.ID AND event_ID = ?");
                    $stmt->bind_param("i", $row['ID']);
                    $stmt->execute();
                    $rate = $stmt->get_result()->fetch_assoc();

                    $output[] = array(
                        'id' => $row['ID'],
                        'title' => $row['name'],
                        'link' => $row['thumbnail'],
                        'summary' => $row['summary'],
                        'rate' => $rate['rate'],
                        'comments' => $rate['comments'],
                    );
                }

                $activityCountry = '香港';
                $activityType = '潛水';
            }

            /* 滑翔傘 */
            if ($activitiesSelection == 'hotHkParagliding') {
                $stmt = $this->sqlcon->prepare("SELECT ID, review, state, name, country, summary, thumbnail, create_time, type FROM Event WHERE review = 1 AND state = 1 AND type = 2 AND country = 'HK' AND tag LIKE '%滑翔%'");
                if (!$stmt->execute()) {
                    return array(
                        'code' => 500,
                        'Title' => showText('Error_Page.something_happened'),
                        'Message' => $stmt->error,
                    );
                }
                $rs = $stmt->get_result();
                while ($row = $rs->fetch_assoc()) {
                    $stmt->prepare("SELECT ROUND(SUM(r.rate)/COUNT(*), 1) AS 'rate', COUNT(*) AS 'comments' FROM Book_review r, Book_event b WHERE r.Book_ID = b.ID AND event_ID = ?");
                    $stmt->bind_param("i", $row['ID']);
                    $stmt->execute();
                    $rate = $stmt->get_result()->fetch_assoc();

                    $output[] = array(
                        'id' => $row['ID'],
                        'title' => $row['name'],
                        'link' => $row['thumbnail'],
                        'summary' => $row['summary'],
                        'rate' => $rate['rate'],
                        'comments' => $rate['comments'],
                    );
                }

                $activityCountry = '香港';
                $activityType = '滑翔傘';

            }

            /* 遠足 */
            if ($activitiesSelection == 'hotHkHiking') {
                $stmt = $this->sqlcon->prepare("SELECT ID, review, state, name, country, summary, thumbnail, create_time, type FROM Event WHERE review = 1 AND state = 1 AND type = 1 AND country = 'HK' AND tag LIKE '%遠足%'");
                if (!$stmt->execute()) {
                    return array(
                        'code' => 500,
                        'Title' => showText('Error_Page.something_happened'),
                        'Message' => $stmt->error,
                    );
                }
                $rs = $stmt->get_result();
                while ($row = $rs->fetch_assoc()) {
                    $stmt->prepare("SELECT ROUND(SUM(r.rate)/COUNT(*), 1) AS 'rate', COUNT(*) AS 'comments' FROM Book_review r, Book_event b WHERE r.Book_ID = b.ID AND event_ID = ?");
                    $stmt->bind_param("i", $row['ID']);
                    $stmt->execute();
                    $rate = $stmt->get_result()->fetch_assoc();

                    $output[] = array(
                        'id' => $row['ID'],
                        'title' => $row['name'],
                        'link' => $row['thumbnail'],
                        'summary' => $row['summary'],
                        'rate' => $rate['rate'],
                        'comments' => $rate['comments'],
                    );
                }

                $activityCountry = '香港';
                $activityType = '遠足';
            }

            /* 提供中國熱門 */
            /* 獨木舟 */
            if ($activitiesSelection == 'hotCnCanoeing') {
                $stmt = $this->sqlcon->prepare("SELECT ID, review, state, name, country, summary, thumbnail, create_time, type FROM Event WHERE review = 1 AND state = 1 AND type = 0 AND country = 'CN' AND tag LIKE '%獨木%' AND tag LIKE '%舟%'");
                if (!$stmt->execute()) {
                    return array(
                        'code' => 500,
                        'Title' => showText('Error_Page.something_happened'),
                        'Message' => $stmt->error,
                    );
                }
                $rs = $stmt->get_result();
                while ($row = $rs->fetch_assoc()) {
                    $stmt->prepare("SELECT ROUND(SUM(r.rate)/COUNT(*), 1) AS 'rate', COUNT(*) AS 'comments' FROM Book_review r, Book_event b WHERE r.Book_ID = b.ID AND event_ID = ?");
                    $stmt->bind_param("i", $row['ID']);
                    $stmt->execute();
                    $rate = $stmt->get_result()->fetch_assoc();

                    $output[] = array(
                        'id' => $row['ID'],
                        'title' => $row['name'],
                        'link' => $row['thumbnail'],
                        'summary' => $row['summary'],
                        'rate' => $rate['rate'],
                        'comments' => $rate['comments'],
                    );
                }

                $activityCountry = '中國大陸';
                $activityType = '獨木舟';
            }

            /* 攀岩 */
            if ($activitiesSelection == 'hotCnClimbing') {
                $stmt = $this->sqlcon->prepare("SELECT ID, review, state, name, country, summary, thumbnail, create_time, type FROM Event WHERE review = 1 AND state = 1 AND type = 1 AND country = 'CN' AND tag LIKE '%岩%' AND tag LIKE '%攀岩%'");
                if (!$stmt->execute()) {
                    return array(
                        'code' => 500,
                        'Title' => showText('Error_Page.something_happened'),
                        'Message' => $stmt->error,
                    );
                }
                $rs = $stmt->get_result();
                while ($row = $rs->fetch_assoc()) {
                    $stmt->prepare("SELECT ROUND(SUM(r.rate)/COUNT(*), 1) AS 'rate', COUNT(*) AS 'comments' FROM Book_review r, Book_event b WHERE r.Book_ID = b.ID AND event_ID = ?");
                    $stmt->bind_param("i", $row['ID']);
                    $stmt->execute();
                    $rate = $stmt->get_result()->fetch_assoc();

                    $output[] = array(
                        'id' => $row['ID'],
                        'title' => $row['name'],
                        'link' => $row['thumbnail'],
                        'summary' => $row['summary'],
                        'rate' => $rate['rate'],
                        'comments' => $rate['comments'],
                    );
                }

                $activityCountry = '中國大陸';
                $activityType = '攀岩';
            }

            /* 熱氣球 */
            if ($activitiesSelection == 'hotCnHotAirBalloon') {
                $stmt = $this->sqlcon->prepare("SELECT ID, review, state, name, country, summary, thumbnail, create_time, type FROM Event WHERE review = 1 AND state = 1 AND type = 2 AND country = 'CN' AND tag LIKE '%熱氣%'");
                if (!$stmt->execute()) {
                    return array(
                        'code' => 500,
                        'Title' => showText('Error_Page.something_happened'),
                        'Message' => $stmt->error,
                    );
                }
                $rs = $stmt->get_result();
                while ($row = $rs->fetch_assoc()) {
                    $stmt->prepare("SELECT ROUND(SUM(r.rate)/COUNT(*), 1) AS 'rate', COUNT(*) AS 'comments' FROM Book_review r, Book_event b WHERE r.Book_ID = b.ID AND event_ID = ?");
                    $stmt->bind_param("i", $row['ID']);
                    $stmt->execute();
                    $rate = $stmt->get_result()->fetch_assoc();

                    $output[] = array(
                        'id' => $row['ID'],
                        'title' => $row['name'],
                        'link' => $row['thumbnail'],
                        'summary' => $row['summary'],
                        'rate' => $rate['rate'],
                        'comments' => $rate['comments'],
                    );
                }

                $activityCountry = '中國大陸';
                $activityType = '熱氣球';
            }

            /* 登山 */
            if ($activitiesSelection == 'hotCnMountaineering') {
                $stmt = $this->sqlcon->prepare("SELECT ID, review, state, name, country, summary, thumbnail, create_time, type FROM Event WHERE review = 1 AND state = 1 AND type = 1 AND country = 'CN' AND tag LIKE '%登山%'");
                if (!$stmt->execute()) {
                    return array(
                        'code' => 500,
                        'Title' => showText('Error_Page.something_happened'),
                        'Message' => $stmt->error,
                    );
                }
                $rs = $stmt->get_result();
                while ($row = $rs->fetch_assoc()) {
                    $stmt->prepare("SELECT ROUND(SUM(r.rate)/COUNT(*), 1) AS 'rate', COUNT(*) AS 'comments' FROM Book_review r, Book_event b WHERE r.Book_ID = b.ID AND event_ID = ?");
                    $stmt->bind_param("i", $row['ID']);
                    $stmt->execute();
                    $rate = $stmt->get_result()->fetch_assoc();

                    $output[] = array(
                        'id' => $row['ID'],
                        'title' => $row['name'],
                        'link' => $row['thumbnail'],
                        'summary' => $row['summary'],
                        'rate' => $rate['rate'],
                        'comments' => $rate['comments'],
                    );
                }

                $activityCountry = '中國大陸';
                $activityType = '登山';
            }

            /* 滑翔傘 */
            if ($activitiesSelection == 'hotCnParagliding') {
                $stmt = $this->sqlcon->prepare("SELECT ID, review, state, name, country, summary, thumbnail, create_time, type FROM Event WHERE review = 1 AND state = 1 AND type = 2 AND country = 'CN' AND tag LIKE '%滑翔%'");
                if (!$stmt->execute()) {
                    return array(
                        'code' => 500,
                        'Title' => showText('Error_Page.something_happened'),
                        'Message' => $stmt->error,
                    );
                }
                $rs = $stmt->get_result();
                while ($row = $rs->fetch_assoc()) {
                    $stmt->prepare("SELECT ROUND(SUM(r.rate)/COUNT(*), 1) AS 'rate', COUNT(*) AS 'comments' FROM Book_review r, Book_event b WHERE r.Book_ID = b.ID AND event_ID = ?");
                    $stmt->bind_param("i", $row['ID']);
                    $stmt->execute();
                    $rate = $stmt->get_result()->fetch_assoc();

                    $output[] = array(
                        'id' => $row['ID'],
                        'title' => $row['name'],
                        'link' => $row['thumbnail'],
                        'summary' => $row['summary'],
                        'rate' => $rate['rate'],
                        'comments' => $rate['comments'],
                    );
                }

                $activityCountry = '中國大陸';
                $activityType = '滑翔傘';
            }

            /* 滑雪 */
            if ($activitiesSelection == 'hotCnSkiing') {
                $stmt = $this->sqlcon->prepare("SELECT ID, review, state, name, country, summary, thumbnail, create_time, type FROM Event WHERE review = 1 AND state = 1 AND type = 1 AND country = 'CN' AND tag LIKE '%滑雪%'");
                if (!$stmt->execute()) {
                    return array(
                        'code' => 500,
                        'Title' => showText('Error_Page.something_happened'),
                        'Message' => $stmt->error,
                    );
                }
                $rs = $stmt->get_result();
                while ($row = $rs->fetch_assoc()) {
                    $stmt->prepare("SELECT ROUND(SUM(r.rate)/COUNT(*), 1) AS 'rate', COUNT(*) AS 'comments' FROM Book_review r, Book_event b WHERE r.Book_ID = b.ID AND event_ID = ?");
                    $stmt->bind_param("i", $row['ID']);
                    $stmt->execute();
                    $rate = $stmt->get_result()->fetch_assoc();

                    $output[] = array(
                        'id' => $row['ID'],
                        'title' => $row['name'],
                        'link' => $row['thumbnail'],
                        'summary' => $row['summary'],
                        'rate' => $rate['rate'],
                        'comments' => $rate['comments'],
                    );
                }

                $activityCountry = '中國大陸';
                $activityType = '滑雪';
            }

            /* 遠足 */
            if ($activitiesSelection == 'hotCnHiking') {
                $stmt = $this->sqlcon->prepare("SELECT ID, review, state, name, country, summary, thumbnail, create_time, type FROM Event WHERE review = 1 AND state = 1 AND type = 1 AND country = 'CN' AND tag LIKE '%遠足%'");
                if (!$stmt->execute()) {
                    return array(
                        'code' => 500,
                        'Title' => showText('Error_Page.something_happened'),
                        'Message' => $stmt->error,
                    );
                }
                $rs = $stmt->get_result();
                while ($row = $rs->fetch_assoc()) {
                    $stmt->prepare("SELECT ROUND(SUM(r.rate)/COUNT(*), 1) AS 'rate', COUNT(*) AS 'comments' FROM Book_review r, Book_event b WHERE r.Book_ID = b.ID AND event_ID = ?");
                    $stmt->bind_param("i", $row['ID']);
                    $stmt->execute();
                    $rate = $stmt->get_result()->fetch_assoc();

                    $output[] = array(
                        'id' => $row['ID'],
                        'title' => $row['name'],
                        'link' => $row['thumbnail'],
                        'summary' => $row['summary'],
                        'rate' => $rate['rate'],
                        'comments' => $rate['comments'],
                    );
                }

                $activityCountry = '中國大陸';
                $activityType = '遠足';
            }

            /* 提供澳門熱門 */
            /* 笨豬跳 */
            if ($activitiesSelection == 'hotMoBungy') {
                $stmt = $this->sqlcon->prepare("SELECT ID, review, state, name, country, summary, thumbnail, create_time, type FROM Event WHERE review = 1 AND state = 1 AND type = 2 AND country = 'MO' AND tag LIKE '%笨豬跳%'");
                if (!$stmt->execute()) {
                    return array(
                        'code' => 500,
                        'Title' => showText('Error_Page.something_happened'),
                        'Message' => $stmt->error,
                    );
                }
                $rs = $stmt->get_result();
                while ($row = $rs->fetch_assoc()) {
                    $stmt->prepare("SELECT ROUND(SUM(r.rate)/COUNT(*), 1) AS 'rate', COUNT(*) AS 'comments' FROM Book_review r, Book_event b WHERE r.Book_ID = b.ID AND event_ID = ?");
                    $stmt->bind_param("i", $row['ID']);
                    $stmt->execute();
                    $rate = $stmt->get_result()->fetch_assoc();

                    $output[] = array(
                        'id' => $row['ID'],
                        'title' => $row['name'],
                        'link' => $row['thumbnail'],
                        'summary' => $row['summary'],
                        'rate' => $rate['rate'],
                        'comments' => $rate['comments'],
                    );
                }

                $activityCountry = '澳門';
                $activityType = '笨豬跳';
            }

            /* 攀岩 */
            if ($activitiesSelection == 'hotMoClimbing') {
                $stmt = $this->sqlcon->prepare("SELECT ID, review, state, name, country, summary, thumbnail, create_time, type FROM Event WHERE review = 1 AND state = 1 AND type = 1 AND country = 'MO' AND tag LIKE '%岩%' AND tag LIKE '%攀岩%' ");
                if (!$stmt->execute()) {
                    return array(
                        'code' => 500,
                        'Title' => showText('Error_Page.something_happened'),
                        'Message' => $stmt->error,
                    );
                }
                $rs = $stmt->get_result();
                while ($row = $rs->fetch_assoc()) {
                    $stmt->prepare("SELECT ROUND(SUM(r.rate)/COUNT(*), 1) AS 'rate', COUNT(*) AS 'comments' FROM Book_review r, Book_event b WHERE r.Book_ID = b.ID AND event_ID = ?");
                    $stmt->bind_param("i", $row['ID']);
                    $stmt->execute();
                    $rate = $stmt->get_result()->fetch_assoc();

                    $output[] = array(
                        'id' => $row['ID'],
                        'title' => $row['name'],
                        'link' => $row['thumbnail'],
                        'summary' => $row['summary'],
                        'rate' => $rate['rate'],
                        'comments' => $rate['comments'],
                    );
                }

                $activityCountry = '澳門';
                $activityType = '攀岩';
            }

            /* 提供台灣熱門 */
            /* 獨木舟 */
            if ($activitiesSelection == 'hotTwCanoeing') {
                $stmt = $this->sqlcon->prepare("SELECT ID, review, state, name, country, summary, thumbnail, create_time, type FROM Event WHERE review = 1 AND state = 1 AND type = 0 AND country = 'TW' AND tag LIKE '%獨木%' AND tag LIKE '%舟%'");
                if (!$stmt->execute()) {
                    return array(
                        'code' => 500,
                        'Title' => showText('Error_Page.something_happened'),
                        'Message' => $stmt->error,
                    );
                }
                $rs = $stmt->get_result();
                while ($row = $rs->fetch_assoc()) {
                    $stmt->prepare("SELECT ROUND(SUM(r.rate)/COUNT(*), 1) AS 'rate', COUNT(*) AS 'comments' FROM Book_review r, Book_event b WHERE r.Book_ID = b.ID AND event_ID = ?");
                    $stmt->bind_param("i", $row['ID']);
                    $stmt->execute();
                    $rate = $stmt->get_result()->fetch_assoc();

                    $output[] = array(
                        'id' => $row['ID'],
                        'title' => $row['name'],
                        'link' => $row['thumbnail'],
                        'summary' => $row['summary'],
                        'rate' => $rate['rate'],
                        'comments' => $rate['comments'],
                    );
                }

                $activityCountry = '台灣';
                $activityType = '獨木舟';
            }

            /* 攀岩 */
            if ($activitiesSelection == 'hotTwClimbing') {
                $stmt = $this->sqlcon->prepare("SELECT ID, review, state, name, country, summary, thumbnail, create_time, type FROM Event WHERE review = 1 AND state = 1 AND type = 1 AND country = 'TW' AND tag LIKE '%岩%' AND tag LIKE '%攀岩%'");
                if (!$stmt->execute()) {
                    return array(
                        'code' => 500,
                        'Title' => showText('Error_Page.something_happened'),
                        'Message' => $stmt->error,
                    );
                }
                $rs = $stmt->get_result();
                while ($row = $rs->fetch_assoc()) {
                    $stmt->prepare("SELECT ROUND(SUM(r.rate)/COUNT(*), 1) AS 'rate', COUNT(*) AS 'comments' FROM Book_review r, Book_event b WHERE r.Book_ID = b.ID AND event_ID = ?");
                    $stmt->bind_param("i", $row['ID']);
                    $stmt->execute();
                    $rate = $stmt->get_result()->fetch_assoc();

                    $output[] = array(
                        'id' => $row['ID'],
                        'title' => $row['name'],
                        'link' => $row['thumbnail'],
                        'summary' => $row['summary'],
                        'rate' => $rate['rate'],
                        'comments' => $rate['comments'],
                    );
                }

                $activityCountry = '台灣';
                $activityType = '攀岩';
            }

            /* 潛水 */
            if ($activitiesSelection == 'hotTwDiving') {
                $stmt = $this->sqlcon->prepare("SELECT ID, review, state, name, country, summary, thumbnail, create_time, type FROM Event WHERE review = 1 AND state = 1 AND type = 0 AND country = 'TW' AND tag LIKE '%潛%'");
                if (!$stmt->execute()) {
                    return array(
                        'code' => 500,
                        'Title' => showText('Error_Page.something_happened'),
                        'Message' => $stmt->error,
                    );
                }
                $rs = $stmt->get_result();
                while ($row = $rs->fetch_assoc()) {
                    $stmt->prepare("SELECT ROUND(SUM(r.rate)/COUNT(*), 1) AS 'rate', COUNT(*) AS 'comments' FROM Book_review r, Book_event b WHERE r.Book_ID = b.ID AND event_ID = ?");
                    $stmt->bind_param("i", $row['ID']);
                    $stmt->execute();
                    $rate = $stmt->get_result()->fetch_assoc();

                    $output[] = array(
                        'id' => $row['ID'],
                        'title' => $row['name'],
                        'link' => $row['thumbnail'],
                        'summary' => $row['summary'],
                        'rate' => $rate['rate'],
                        'comments' => $rate['comments'],
                    );
                }

                $activityCountry = '台灣';
                $activityType = '潛水';
            }

            /* 登山 */
            if ($activitiesSelection == 'hotTwMountaineering') {
                $stmt = $this->sqlcon->prepare("SELECT ID, review, state, name, country, summary, thumbnail, create_time, type FROM Event WHERE review = 1 AND state = 1 AND type = 1 AND country = 'TW' AND tag LIKE '%登山%'");
                if (!$stmt->execute()) {
                    return array(
                        'code' => 500,
                        'Title' => showText('Error_Page.something_happened'),
                        'Message' => $stmt->error,
                    );
                }
                $rs = $stmt->get_result();
                while ($row = $rs->fetch_assoc()) {
                    $stmt->prepare("SELECT ROUND(SUM(r.rate)/COUNT(*), 1) AS 'rate', COUNT(*) AS 'comments' FROM Book_review r, Book_event b WHERE r.Book_ID = b.ID AND event_ID = ?");
                    $stmt->bind_param("i", $row['ID']);
                    $stmt->execute();
                    $rate = $stmt->get_result()->fetch_assoc();

                    $output[] = array(
                        'id' => $row['ID'],
                        'title' => $row['name'],
                        'link' => $row['thumbnail'],
                        'summary' => $row['summary'],
                        'rate' => $rate['rate'],
                        'comments' => $rate['comments'],
                    );
                }

                $activityCountry = '台灣';
                $activityType = '登山';
            }

            /* 跳傘 */
            if ($activitiesSelection == 'hotTwParachute') {
                $stmt = $this->sqlcon->prepare("SELECT ID, review, state, name, country, summary, thumbnail, create_time, type FROM Event WHERE review = 1 AND state = 1 AND type = 2 AND country = 'TW' AND tag LIKE '跳傘'");
                if (!$stmt->execute()) {
                    return array(
                        'code' => 500,
                        'Title' => showText('Error_Page.something_happened'),
                        'Message' => $stmt->error,
                    );
                }
                $rs = $stmt->get_result();
                while ($row = $rs->fetch_assoc()) {
                    $stmt->prepare("SELECT ROUND(SUM(r.rate)/COUNT(*), 1) AS 'rate', COUNT(*) AS 'comments' FROM Book_review r, Book_event b WHERE r.Book_ID = b.ID AND event_ID = ?");
                    $stmt->bind_param("i", $row['ID']);
                    $stmt->execute();
                    $rate = $stmt->get_result()->fetch_assoc();

                    $output[] = array(
                        'id' => $row['ID'],
                        'title' => $row['name'],
                        'link' => $row['thumbnail'],
                        'summary' => $row['summary'],
                        'rate' => $rate['rate'],
                        'comments' => $rate['comments'],
                    );
                }

                $activityCountry = '台灣';
                $activityType = '跳傘';
            }

            /* 滑翔傘 */
            if ($activitiesSelection == 'hotTwParagliding') {
                $stmt = $this->sqlcon->prepare("SELECT ID, review, state, name, country, summary, thumbnail, create_time, type FROM Event WHERE review = 1 AND state = 1 AND type = 2 AND country = 'TW' AND tag LIKE '%滑翔%'");
                if (!$stmt->execute()) {
                    return array(
                        'code' => 500,
                        'Title' => showText('Error_Page.something_happened'),
                        'Message' => $stmt->error,
                    );
                }
                $rs = $stmt->get_result();
                while ($row = $rs->fetch_assoc()) {
                    $stmt->prepare("SELECT ROUND(SUM(r.rate)/COUNT(*), 1) AS 'rate', COUNT(*) AS 'comments' FROM Book_review r, Book_event b WHERE r.Book_ID = b.ID AND event_ID = ?");
                    $stmt->bind_param("i", $row['ID']);
                    $stmt->execute();
                    $rate = $stmt->get_result()->fetch_assoc();

                    $output[] = array(
                        'id' => $row['ID'],
                        'title' => $row['name'],
                        'link' => $row['thumbnail'],
                        'summary' => $row['summary'],
                        'rate' => $rate['rate'],
                        'comments' => $rate['comments'],
                    );
                }

                $activityCountry = '台灣';
                $activityType = '滑翔傘';
            }

            /* 遠足 */
            if ($activitiesSelection == 'hotTwHiking') {
                $stmt = $this->sqlcon->prepare("SELECT ID, review, state, name, country, summary, thumbnail, create_time, type FROM Event WHERE review = 1 AND state = 1 AND type = 1 AND country = 'TW' AND tag LIKE '%遠足%'");
                if (!$stmt->execute()) {
                    return array(
                        'code' => 500,
                        'Title' => showText('Error_Page.something_happened'),
                        'Message' => $stmt->error,
                    );
                }
                $rs = $stmt->get_result();
                while ($row = $rs->fetch_assoc()) {
                    $stmt->prepare("SELECT ROUND(SUM(r.rate)/COUNT(*), 1) AS 'rate', COUNT(*) AS 'comments' FROM Book_review r, Book_event b WHERE r.Book_ID = b.ID AND event_ID = ?");
                    $stmt->bind_param("i", $row['ID']);
                    $stmt->execute();
                    $rate = $stmt->get_result()->fetch_assoc();

                    $output[] = array(
                        'id' => $row['ID'],
                        'title' => $row['name'],
                        'link' => $row['thumbnail'],
                        'summary' => $row['summary'],
                        'rate' => $rate['rate'],
                        'comments' => $rate['comments'],
                    );
                }

                $activityCountry = '台灣';
                $activityType = '遠足';
            }

            return array(
                'code' => 200,
                'data' => $output,
                'country' => $activityCountry,
                'type' => $activityType,
            );
        } else {

            $hkOutput = [];
            $cnOutput = [];
            $moOutput = [];
            $twOutput = [];
            $country = ['香港', '中國大陸', '澳門', '台灣'];

            /* 香港最新 */
            $stmt = $this->sqlcon->prepare("SELECT ID, review, state, name, summary, country, thumbnail, create_time FROM Event WHERE review = 1 AND state = 1 AND country = 'HK' ORDER BY create_time DESC LIMIT 5");
            if (!$stmt->execute()) {
                return array(
                        'code' => 500,
                        'Title' => showText('Error_Page.something_happened'),
                        'Message' => $stmt->error,
                    );
            }
            $rs = $stmt->get_result();
            while($row = $rs->fetch_assoc()) {
                $hkOutput[] = array(
                    'id' => $row['ID'],
                    'title' => $row['name'],
                    'link' => $row['thumbnail'],
                    'summary' => $row['summary'],
                );
            }

            /* 中國最新 */
            $stmt = $this->sqlcon->prepare("SELECT ID, review, state, name, summary, country, thumbnail, create_time FROM Event WHERE review = 1 AND state = 1 AND country = 'CN' ORDER BY create_time DESC LIMIT 5");
            if (!$stmt->execute()) {
                return array(
                        'code' => 500,
                        'Title' => showText('Error_Page.something_happened'),
                        'Message' => $stmt->error,
                    );
            }
            $rs = $stmt->get_result();
            while($row = $rs->fetch_assoc()) {
                $cnOutput[] = array(
                    'id' => $row['ID'],
                    'title' => $row['name'],
                    'link' => $row['thumbnail'],
                    'summary' => $row['summary'],
                );
            }

            /* 澳門最新 */
            $stmt = $this->sqlcon->prepare("SELECT ID, review, state, name, summary, country, thumbnail, create_time FROM Event WHERE review = 1 AND state = 1 AND country = 'MO' ORDER BY create_time DESC LIMIT 5");
            if (!$stmt->execute()) {
                return array(
                        'code' => 500,
                        'Title' => showText('Error_Page.something_happened'),
                        'Message' => $stmt->error,
                    );
            }
            $rs = $stmt->get_result();
            while($row = $rs->fetch_assoc()) {
                $moOutput[] = array(
                    'id' => $row['ID'],
                    'title' => $row['name'],
                    'link' => $row['thumbnail'],
                    'summary' => $row['summary'],
                );
            }

            /* 台灣最新 */
            $stmt = $this->sqlcon->prepare("SELECT ID, review, state, name, summary, country, thumbnail, create_time FROM Event WHERE review = 1 AND state = 1 AND country = 'TW' ORDER BY create_time DESC LIMIT 5");
            if (!$stmt->execute()) {
                return array(
                        'code' => 500,
                        'Title' => showText('Error_Page.something_happened'),
                        'Message' => $stmt->error,
                    );
            }
            $rs = $stmt->get_result();
            while($row = $rs->fetch_assoc()) {
                $twOutput[] = array(
                    'id' => $row['ID'],
                    'title' => $row['name'],
                    'link' => $row['thumbnail'],
                    'summary' => $row['summary'],
                );
            }

            $output = array($hkOutput, $cnOutput, $moOutput, $twOutput);


            return array(
                'code' => 200,
                'data' => $output,
                'country' => $country,
            );
        }
    }

    /* path輸出 */
    function path(): string {
        return '<li class="breadcrumb-item active">' . showText("index.home") . '</li>';
    }

    /* 取得頁面標題 */
    public function get_Title(): string {
        return showText('index.title');
    }

    /* 取得頁首標題 */
    public function get_Head(): string {
        return showText("index.home");
    }

}