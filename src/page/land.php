<?php
/**
 * Copyright (c) 2020.
 * Create by cocomine
 */

namespace page;

use cocomine\IPage;
use mysqli;
use panel\apis\media;

/**
 * Class air
 * @package cocopixelmc\Page
 */

class land implements IPage {
    private mysqli $sqlcon;

    /**
     * home constructor.
     * sql連接
     * @param $sqlcon
     */
    function __construct($sqlcon) {
        $this->sqlcon = $sqlcon;
    }

    /**
     * @inheritDoc
     */
    public function access(bool $isAuth, int $role, bool $isPost): int
    {
        return 200;
    }

    public function showPage(): string
    {
        $Text = showText('index.Content');

        /* json 語言 */
        $jsonLang = json_encode(array());

        /* 初始提取 */
        $allActivities = '';

        $stmt = $this->sqlcon->prepare("SELECT ID, review, state, name, summary, thumbnail, create_time, type FROM Event WHERE review = 1 AND state = 1 AND type = 1");
        if (!$stmt->execute()) {
            return 'Database Error!';
        }

        $rs = $stmt->get_result();
        while($row = $rs->fetch_assoc()) {
            $allActivities .= "<div class='col-auto'><div class='item'><div class='card card-block mx-2' style='min-width: 300px;'>";
            $allActivities .= "<div class='ratio ratio-4x3 position-relative'><div class='overflow-hidden card-img-top'><div class='media-list-center'>";
            $allActivities .= "<img src='panel/api/media/" .$row['thumbnail']. "' class='owl-lazy' alt='".$row['thumbnail']."'></div></div></div>";

            $allActivities .= "<div class='card-body'><h5 class='card-title'>".$row['name']."</h5>";
            $allActivities .= "<p class='card-text'>".$row['summary']."</p><div class='row align-items-center'><div class='col-auto'>";
            $allActivities .= "<i class='fs-10 fa-solid fa-star text-warning'></i><span id='airRatingScore' class='fs-10'>5.0</span>";
            $allActivities .= "</div></div><a href='activity_details/".$row['ID']."' class='btn btn-primary stretched-link btn-rounded'>了解更多</a>";
            $allActivities .= " </div></div></div></div>";
        }

        return <<<body
<link rel="stylesheet" href="/assets/css/myself/page/land.css">
<pre id='langJson' style='display: none'>$jsonLang</pre>
<div id='landActivitiesBackground' class="position-relative">
    <div class="row justify-content-center align-items-center">
        <div class="col-auto">
            <h5>探索大地 感受自然</h5>
            <nav class="navbar navbar-expand-lg bg-body-tertiary">
              <div class="container-fluid">
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                  <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                      <button type="button" class="btn btn-light btn-lg btn-rounded me-2" id="allLandBtn">全部</button>
                    </li>
                    <li class="nav-item">
                      <button type="button" class="btn btn-light btn-lg btn-rounded me-2" id="mountaineeringBtn">登山</button>
                    </li>
                    <li class="nav-item">
                      <button type="button" class="btn btn-light btn-lg btn-rounded me-2" id="hikingBtn">遠足</button>
                    </li>
                    <li class="nav-item">
                      <button type="button" class="btn btn-light btn-lg btn-rounded me-2" id="climbingBtn">攀登</button>
                    </li>
                    <li class="nav-item">
                      <button type="button" class="btn btn-light btn-lg btn-rounded me-2" id="skiingBtn">滑雪</button>
                    </li>
                    <li class="nav-item">
                      <button type="button" class="btn btn-light btn-lg btn-rounded me-2" id="otherlandBtn">其他</button>
                    </li>
                  </ul>
                </div>
              </div>
            </nav>    
        </div>
    </div>
</div>
body . <<<body
<div class="container mt-4">
    <div class="row row-cols-1 row-cols-md-4 g-4" id="landEvent">
    $allActivities
    </div>
</div>
body . <<<body
<script>
loadModules(['myself/datepicker', 'myself/page/land'])
</script>
body;
    }

    public function post(array $data): array
    {
        global $auth;
        $output = [];
        $activitiesSelection = $data['activitiesSelection'];

        /* 提供全部陸上活動 */
        if($activitiesSelection == 'allLandBtn') {
            $stmt = $this->sqlcon->prepare("SELECT ID, review, state, name, summary, thumbnail, create_time, type FROM Event WHERE review = 1 AND state = 1 AND type = 1");
            if (!$stmt->execute()) {
                return 'Database Error!';
            }
            $rs = $stmt->get_result();
            while($row = $rs->fetch_assoc()) {
                $output[] = array(
                    'id' => $row['ID'],
                    'title' => $row['name'],
                    'link' => $row['thumbnail'],
                    'summary' => $row['summary'],
                    'serverName' => $_SERVER['SERVER_NAME'],
                );
            }
        }

        /* 提供登山活動 */
        if($activitiesSelection == 'mountaineeringBtn') {
            $stmt = $this->sqlcon->prepare("SELECT ID, review, state, name, summary, thumbnail, create_time, type FROM Event WHERE review = 1 AND state = 1 AND type = 1 AND tag LIKE '%登山%'");
            if (!$stmt->execute()) {
                return 'Database Error!';
            }
            $rs = $stmt->get_result();
            while($row = $rs->fetch_assoc()) {
                $output[] = array(
                    'id' => $row['ID'],
                    'title' => $row['name'],
                    'link' => $row['thumbnail'],
                    'summary' => $row['summary'],
                    'serverName' => $_SERVER['SERVER_NAME'],
                );
            }
        }

        /* 提供遠足活動 */
        if($activitiesSelection == 'hikingBtn') {
            $stmt = $this->sqlcon->prepare("SELECT ID, review, state, name, summary, thumbnail, create_time, type FROM Event WHERE review = 1 AND state = 1 AND type = 1 AND tag LIKE '%遠足%'");
            if (!$stmt->execute()) {
                return 'Database Error!';
            }
            $rs = $stmt->get_result();
            while($row = $rs->fetch_assoc()) {
                $output[] = array(
                    'id' => $row['ID'],
                    'title' => $row['name'],
                    'link' => $row['thumbnail'],
                    'summary' => $row['summary'],
                    'serverName' => $_SERVER['SERVER_NAME'],
                );
            }
        }

        /* 提供攀岩活動 */
        if($activitiesSelection == 'climbingBtn') {
            $stmt = $this->sqlcon->prepare("SELECT ID, review, state, name, summary, thumbnail, create_time, type FROM Event WHERE review = 1 AND state = 1 AND type = 1 AND tag LIKE '%岩%' OR tag LIKE '%攀岩%'");
            if (!$stmt->execute()) {
                return 'Database Error!';
            }
            $rs = $stmt->get_result();
            while($row = $rs->fetch_assoc()) {
                $output[] = array(
                    'id' => $row['ID'],
                    'title' => $row['name'],
                    'link' => $row['thumbnail'],
                    'summary' => $row['summary'],
                    'serverName' => $_SERVER['SERVER_NAME'],
                );
            }
        }

        /* 提供滑雪活動 */
        if($activitiesSelection == 'skiingBtn') {
            $stmt = $this->sqlcon->prepare("SELECT ID, review, state, name, summary, thumbnail, create_time, type FROM Event WHERE review = 1 AND state = 1 AND type = 1 AND tag LIKE '%滑雪%'");
            if (!$stmt->execute()) {
                return 'Database Error!';
            }
            $rs = $stmt->get_result();
            while($row = $rs->fetch_assoc()) {
                $output[] = array(
                    'id' => $row['ID'],
                    'title' => $row['name'],
                    'link' => $row['thumbnail'],
                    'summary' => $row['summary'],
                    'serverName' => $_SERVER['SERVER_NAME'],
                );
            }
        }

        /* 提供其他陸上活動 */
        if($activitiesSelection == 'otherlandBtn') {
            $stmt = $this->sqlcon->prepare("SELECT ID, review, state, name, summary, thumbnail, create_time, type FROM Event WHERE review = 1 AND state = 1 AND type = 1 AND tag NOT LIKE '%滑雪%' AND tag NOT LIKE '%岩%' AND tag NOT LIKE '%攀岩%' AND tag NOT LIKE '%遠足%' AND tag NOT LIKE '%登山%'");
            if (!$stmt->execute()) {
                return 'Database Error!';
            }
            $rs = $stmt->get_result();
            while($row = $rs->fetch_assoc()) {
                $output[] = array(
                    'id' => $row['ID'],
                    'title' => $row['name'],
                    'link' => $row['thumbnail'],
                    'summary' => $row['summary'],
                    'serverName' => $_SERVER['SERVER_NAME'],
                );
            }
        }

        return array(
            'code' => 200,
            'data' => $output,
        );
    }

    public function path(): string
    {
        return '<li class="breadcrumb-item active">陸上活動</li>';
    }

    public function get_Title(): string
    {
        return "陸上活動|X-Travel";
    }

    public function get_Head(): string
    {
        return "陸上活動";
    }
}