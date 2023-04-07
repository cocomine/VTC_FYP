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
 * Class water_activites
 * @package cocopixelmc\Page
 */
class water implements IPage {
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

        $stmt = $this->sqlcon->prepare("SELECT ev.ID, ev.review, ev.state, ev.name, ev.summary, ev.thumbnail, ev.create_time, ev.type FROM Event AS ev WHERE review = 1 AND state = 1 AND type = 0");
        if (!$stmt->execute()) {
            return 'Database Error!';
        }

        $rs = $stmt->get_result();
        while($row = $rs->fetch_assoc()) {
            $allActivities .= "<div class='col-auto'><div class='item'><div class='card card-block mx-2' style='min-width: 300px;'>";
            $allActivities .= "<div class='ratio ratio-4x3 position-relative'><div class='overflow-hidden card-img-top'><div class='media-list-center'>";
            $allActivities .= "<img src='/panel/api/media/" .$row['thumbnail']. "' class='owl-lazy' alt='".$row['thumbnail']."'></div></div></div>";
            $allActivities .= "<div class='card-body'><h5 class='card-title'>".$row['name']."</h5>";
            $allActivities .= "<p class='card-text'>".$row['summary']."</p><div class='row align-items-center'><div class='col-auto'>";

            $stmt->prepare("SELECT ROUND(SUM(r.rate)/COUNT(*), 1) AS 'rate', COUNT(*) AS 'total', COUNT(*) AS 'comments' FROM Book_review r, Book_event b WHERE r.Book_ID = b.ID AND event_ID = ?");
            $stmt->bind_param("i", $row['ID']);
            $stmt->execute();
            $rate = $stmt->get_result()->fetch_assoc();

            $row['rate'] = $rate['rate'];
            $row['total'] = $rate['total'];
            $row['comments'] = $rate['comments'];

            if ($row['comments'] == 0) {
                $comments = $row['comments'] . '則評論';
            } else {
                $comments = '暫無評論';
            }

            if($row['rate'] != null) {
                if($row['rate'] < 4) {
                    $allActivities .= "<i class='fs-10 fa-solid fa-star text-warning'></i><span id='waterRatingScore' class='fs-10'>".$row['rate']."</span><span class='fs-5'>/5.0</span><span class='fs-10'>&nbsp&nbsp&nbsp". $comments ."</span>";
                } else {
                    $allActivities .= "<i class='fs-10 fa-solid fa-star text-warning'></i><span id='waterRatingScoreOverEqual4' class='fs-10'>".$row['rate']."</span><span class='fs-5'>/5.0</span><span class='fs-10'>&nbsp&nbsp&nbsp". $comments ."</span>";
                }
            } else {
                $allActivities .= "<i class='fs-10 fa-solid fa-star text-warning'></i><span class='fs-10'>-</span><span class='fs-5'>/5.0</span><span class='fs-10'>&nbsp&nbsp&nbsp". $comments ."</span>";
            }

            $allActivities .= "</div></div><a href='/details/".$row['ID']."' class='btn btn-primary stretched-link btn-rounded'>了解更多</a>";
            $allActivities .= " </div></div></div></div>";
        }

        return <<<body
<link rel="stylesheet" href="/assets/css/myself/page/water.css">
<pre id='langJson' style='display: none'>$jsonLang</pre>
<div id='waterActivitiesBackground' class="position-relative">
    <div class="row justify-content-center align-items-center">
        <div class="col-auto">
            <h5>清爽玩樂 水之王國</h5>
            <nav class="navbar navbar-expand-lg bg-body-tertiary">
              <div class="container-fluid">
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                  <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                      <button type="button" class="btn btn-light btn-lg btn-rounded me-2" id="allWaterBtn">全部</button>
                    </li>
                    <li class="nav-item">
                      <button type="button" class="btn btn-light btn-lg btn-rounded me-2" id="divingBtn">潛水</button>
                    </li>
                    <li class="nav-item">
                      <button type="button" class="btn btn-light btn-lg btn-rounded me-2" id="canoeingBtn">獨木舟</button>
                    </li>
                    <li class="nav-item">
                      <button type="button" class="btn btn-light btn-lg btn-rounded me-2" id="riptideBtn">激流</button>
                    </li>
                    <li class="nav-item">
                      <button type="button" class="btn btn-light btn-lg btn-rounded me-2" id="surfBtn">衝浪</button>
                    </li>
                    <li class="nav-item">
                      <button type="button" class="btn btn-light btn-lg btn-rounded me-2" id="otherWaterBtn">其他</button>
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
    <div class="row row-cols-1 row-cols-md-4 g-4" id="waterEvent">
    $allActivities
    </div>
</div>
body . <<<body
<script>
loadModules(['myself/datepicker', 'myself/page/water'])
</script>
body;
    }

    public function post(array $data): array
    {
        global $auth;
        $output = [];
        $activitiesSelection = $data['activitiesSelection'];

        /* 提供全部水上活動 */
        if($activitiesSelection == 'allWaterBtn') {
            $stmt = $this->sqlcon->prepare("SELECT ID, review, state, name, summary, thumbnail, create_time, type FROM Event WHERE review = 1 AND state = 1 AND type = 0");
            if (!$stmt->execute()) {
                return 'Database Error!';
            }
            $rs = $stmt->get_result();
            while ($row = $rs->fetch_assoc()) {
                $stmt->prepare("SELECT ROUND(SUM(r.rate)/COUNT(*), 1) AS 'rate', COUNT(*) AS 'total', COUNT(*) AS 'comments' FROM Book_review r, Book_event b WHERE r.Book_ID = b.ID AND event_ID = ?");
                $stmt->bind_param("i", $row['ID']);
                $stmt->execute();
                $rate = $stmt->get_result()->fetch_assoc();

                $output[] = array(
                    'id' => $row['ID'],
                    'title' => $row['name'],
                    'link' => $row['thumbnail'],
                    'summary' => $row['summary'],
                    'serverName' => $_SERVER['SERVER_NAME'],
                    'rate' => $rate['rate'],
                    'total' => $rate['rate'],
                    'comments' => $rate['comments'],
                );
            }
        }

        /* 提供潛水活動 */
        if($activitiesSelection == 'divingBtn') {
            $stmt = $this->sqlcon->prepare("SELECT ID, review, state, name, summary, thumbnail, create_time, type FROM Event WHERE review = 1 AND state = 1 AND type = 0 AND tag LIKE '%潛%'");
            if (!$stmt->execute()) {
                return 'Database Error!';
            }
            $rs = $stmt->get_result();
            while($row = $rs->fetch_assoc()) {
                $stmt->prepare("SELECT ROUND(SUM(r.rate)/COUNT(*), 1) AS 'rate', COUNT(*) AS 'total', COUNT(*) AS 'comments' FROM Book_review r, Book_event b WHERE r.Book_ID = b.ID AND event_ID = ?");
                $stmt->bind_param("i", $row['ID']);
                $stmt->execute();
                $rate = $stmt->get_result()->fetch_assoc();

                $output[] = array(
                    'id' => $row['ID'],
                    'title' => $row['name'],
                    'link' => $row['thumbnail'],
                    'summary' => $row['summary'],
                    'serverName' => $_SERVER['SERVER_NAME'],
                    'rate' => $rate['rate'],
                    'total' => $rate['rate'],
                    'comments' => $rate['comments'],
                );
            }
        }

        /* 提供獨木舟活動 */
        if($activitiesSelection == 'canoeingBtn') {
            $stmt = $this->sqlcon->prepare("SELECT ID, review, state, name, summary, thumbnail, create_time, type FROM Event WHERE review = 1 AND state = 1 AND type = 0 AND tag LIKE '%獨木%' OR tag LIKE '%舟%'");
            if (!$stmt->execute()) {
                return 'Database Error!';
            }
            $rs = $stmt->get_result();
            while($row = $rs->fetch_assoc()) {
                $stmt->prepare("SELECT ROUND(SUM(r.rate)/COUNT(*), 1) AS 'rate', COUNT(*) AS 'total', COUNT(*) AS 'comments' FROM Book_review r, Book_event b WHERE r.Book_ID = b.ID AND event_ID = ?");
                $stmt->bind_param("i", $row['ID']);
                $stmt->execute();
                $rate = $stmt->get_result()->fetch_assoc();

                $output[] = array(
                    'id' => $row['ID'],
                    'title' => $row['name'],
                    'link' => $row['thumbnail'],
                    'summary' => $row['summary'],
                    'serverName' => $_SERVER['SERVER_NAME'],
                    'rate' => $rate['rate'],
                    'total' => $rate['rate'],
                    'comments' => $rate['comments'],
                );
            }
        }

        /* 提供激流活動 */
        if($activitiesSelection == 'riptideBtn') {
            $stmt = $this->sqlcon->prepare("SELECT ID, review, state, name, summary, thumbnail, create_time, type FROM Event WHERE review = 1 AND state = 1 AND type = 0 AND tag LIKE '%激%' OR tag LIKE '%激流%'");
            if (!$stmt->execute()) {
                return 'Database Error!';
            }
            $rs = $stmt->get_result();
            while($row = $rs->fetch_assoc()) {
                $stmt->prepare("SELECT ROUND(SUM(r.rate)/COUNT(*), 1) AS 'rate', COUNT(*) AS 'total', COUNT(*) AS 'comments' FROM Book_review r, Book_event b WHERE r.Book_ID = b.ID AND event_ID = ?");
                $stmt->bind_param("i", $row['ID']);
                $stmt->execute();
                $rate = $stmt->get_result()->fetch_assoc();

                $output[] = array(
                    'id' => $row['ID'],
                    'title' => $row['name'],
                    'link' => $row['thumbnail'],
                    'summary' => $row['summary'],
                    'serverName' => $_SERVER['SERVER_NAME'],
                    'rate' => $rate['rate'],
                    'total' => $rate['rate'],
                    'comments' => $rate['comments'],
                );
            }
        }

        /* 提供衝浪活動 */
        if($activitiesSelection == 'surfBtn') {
            $stmt = $this->sqlcon->prepare("SELECT ID, review, state, name, summary, thumbnail, create_time, type FROM Event WHERE review = 1 AND state = 1 AND type = 0 AND tag LIKE '%衝浪%' OR tag LIKE '%滑浪%'");
            if (!$stmt->execute()) {
                return 'Database Error!';
            }
            $rs = $stmt->get_result();
            while($row = $rs->fetch_assoc()) {
                $stmt->prepare("SELECT ROUND(SUM(r.rate)/COUNT(*), 1) AS 'rate', COUNT(*) AS 'total', COUNT(*) AS 'comments' FROM Book_review r, Book_event b WHERE r.Book_ID = b.ID AND event_ID = ?");
                $stmt->bind_param("i", $row['ID']);
                $stmt->execute();
                $rate = $stmt->get_result()->fetch_assoc();

                $output[] = array(
                    'id' => $row['ID'],
                    'title' => $row['name'],
                    'link' => $row['thumbnail'],
                    'summary' => $row['summary'],
                    'serverName' => $_SERVER['SERVER_NAME'],
                    'rate' => $rate['rate'],
                    'total' => $rate['rate'],
                    'comments' => $rate['comment'],
                );
            }
        }

        /* 提供其他水上活動 */
        if($activitiesSelection == 'otherWaterBtn') {
            $stmt = $this->sqlcon->prepare("SELECT ID, review, state, name, summary, thumbnail, create_time, type FROM Event WHERE review = 1 AND state = 1 AND type = 0 AND tag NOT LIKE '%衝浪%' AND tag NOT LIKE '%滑浪%' AND tag NOT LIKE '%激%' AND tag NOT LIKE '%激流%' AND tag NOT LIKE '%獨木%' AND tag NOT LIKE '%舟%' AND tag NOT LIKE '%潛%'");
            if (!$stmt->execute()) {
                return 'Database Error!';
            }
            $rs = $stmt->get_result();
            while($row = $rs->fetch_assoc()) {
                $stmt->prepare("SELECT ROUND(SUM(r.rate)/COUNT(*), 1) AS 'rate', COUNT(*) AS 'total', COUNT(*) AS 'comments' FROM Book_review r, Book_event b WHERE r.Book_ID = b.ID AND event_ID = ?");
                $stmt->bind_param("i", $row['ID']);
                $stmt->execute();
                $rate = $stmt->get_result()->fetch_assoc();

                $output[] = array(
                    'id' => $row['ID'],
                    'title' => $row['name'],
                    'link' => $row['thumbnail'],
                    'summary' => $row['summary'],
                    'serverName' => $_SERVER['SERVER_NAME'],
                    'rate' => $rate['rate'],
                    'total' => $rate['rate'],
                    'comments' => $rate['comment'],
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
        return '<li class="breadcrumb-item active">水上活動</li>';
    }

    /**
     * @inheritDoc
     */
    public function get_Title(): string
    {
        return "水上活動|X-Travel";
    }

    /**
     * @inheritDoc
     */
    public function get_Head(): string
    {
        return "水上活動";
    }
}