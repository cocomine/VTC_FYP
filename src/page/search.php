<?php

namespace page;

use cocomine\IPage;
use mysqli;
use panel\apis\media;

class search implements \cocomine\IPage {
    private mysqli $sqlcon;

    private string $search;


    /**
     * search constructor.
     * sql連接
     * @param $sqlcon
     */

    function __construct($sqlcon) {
        $this->sqlcon = $sqlcon;

        if(isset($_POST['search'])) {
            $this->search = $_POST['search'];

        }
    }

    /**
     * @inheritDoc
     */
    public function access(bool $isAuth, int $role, bool $isPost): int {



        return 200;
    }

    /**
     * @inheritDoc
     */
    public function showPage(): string {

        $Text = showText('index.Content');

        /* json 語言 */
        $jsonLang = json_encode(array());


        $searchResult = '';
        $sqlSearch = '%' + $this->search + '%';

        $stmt = $this->sqlcon->prepare("SELECT ID, review, state, name, summary, thumbnail, create_time, type, tag FROM Event WHERE tag LIKE ?");
        $stmt->bind_param("s", $sqlSearch);
        if (!$stmt->execute()) {
            return 'Database Error!';
        }

        $rs = $stmt->get_result();
        while($row = $rs->fetch_assoc()) {
            $searchResult .= "<div class='col-auto'><div class='item'><div class='card card-block mx-2' style='min-width: 300px;'>";
            $searchResult .= "<div class='ratio ratio-4x3 position-relative'><div class='overflow-hidden card-img-top'><div class='media-list-center'>";
            $searchResult .= "<img src='/panel/api/media/" .$row['thumbnail']. "' class='owl-lazy' alt='".$row['thumbnail']."'></div></div></div>";
            $searchResult .= "<div class='card-body'><h5 class='card-title'>".$row['name']."</h5>";
            $searchResult .= "<p class='card-text'>".$row['summary']."</p><div class='row align-items-center'><div class='col-auto'>";

            $stmt->prepare("SELECT ROUND(SUM(r.rate)/COUNT(*), 1) AS 'rate', COUNT(*) AS 'total', COUNT(*) AS 'comments' FROM Book_review r, Book_event b WHERE r.Book_ID = b.ID AND event_ID = ?");
            $stmt->bind_param("i", $row['ID']);
            $stmt->execute();
            $rate = $stmt->get_result()->fetch_assoc();

            $row['rate'] = $rate['rate'];
            $row['total'] = $rate['total'];
            $row['comments'] = $rate['comments'];

            if ($row['comments'] != 0) {
                $comments = $row['comments'] . '則評論';
            } else {
                $comments = '暫無評論';
            }

            if($row['rate'] != null) {
                if($row['rate'] < 4) {
                    $searchResult .= "<i class='fs-10 fa-solid fa-star text-warning'></i><span id='landRatingScore' class='fs-10'>".$row['rate']."</span><span class='fs-5'>/5.0</span><span class='fs-10'>&nbsp&nbsp&nbsp". $comments ."</span>";
                } else {
                    $searchResult .= "<i class='fs-10 fa-solid fa-star text-warning'></i><span id='landRatingScoreOverEqual4' class='fs-10'>".$row['rate']."</span><span class='fs-5'>/5.0</span><span class='fs-10'>&nbsp&nbsp&nbsp". $comments ."</span>";
                }
            } else {
                $searchResult .= "<i class='fs-10 fa-solid fa-star text-warning'></i><span class='fs-10'>-</span><span class='fs-5'>/5.0</span><span class='fs-10'>&nbsp&nbsp&nbsp". $comments ."</span>";
            }

            $searchResult .= "</div></div><a href='/details/".$row['ID']."' class='btn btn-primary stretched-link btn-rounded'>了解更多</a>";
            $searchResult .= " </div></div></div></div>";
        }

        return <<<body
<link rel="stylesheet" href="/assets/css/myself/page/search.css">
<pre id='langJson' style='display: none'>$jsonLang</pre>
<div class="container mt-4">
    <div class="row row-cols-1 row-cols-md-4 g-4">
    $searchResult
    </div>
</div>
body . <<<body
<script>
loadModules(['myself/datepicker', 'myself/page/search'])
</script>
body;

   }

    /**
     * @inheritDoc
     */
    public function post(array $data): array {
        return array();
    }

    /**
     * @inheritDoc
     */
    public function path(): string {
        $search = '';
        if(isset($_POST['search'])) {
            $search = $_POST['search'];
        }
        return '<li class="breadcrumb-item"><a href="/">' . showText("index.home") . '</a></li>'
            . '<li class="breadcrumb-item active">搜尋「'.$_POST['search'].'」</li>';
    }

    /**
     * @inheritDoc
     */
    public function get_Title(): string {
        return "搜尋 | X-Travel";
    }

    /**
     * @inheritDoc
     */
    public function get_Head(): string {
        $search = '';
        if(isset($_POST['search'])) {
            $search = $_POST['search'];
        }
        return "搜尋「".$_POST['search']."」";
    }
}