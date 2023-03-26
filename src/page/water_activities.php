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
class water_activities implements IPage {
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

        return <<<body
<link rel="stylesheet" href="/assets/css/myself/page/water_activities.css">
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
 
    </div>
</div>
body . <<<body
<script>
loadModules(['myself/datepicker', 'myself/page/water_activities'])
</script>
body;
    }

    public function post(array $data): array
    {
        global $auth;
        $output = [];

        $activitiesSelection = $data['activitiesSelection'];
        if($activitiesSelection == 'allWaterBtn') {
            $stmt = $this->sqlcon->prepare("SELECT ID, review, state, name, summary, thumbnail, create_time, type FROM Event WHERE review = 1 AND state = 1 AND type = 0");
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