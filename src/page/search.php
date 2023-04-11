<?php

namespace page;

use cocomine\IPage;
use mysqli;
use panel\apis\media;

class search implements IPage {
    private mysqli $sqlcon;


    /**
     * search constructor.
     * sql連接
     * @param $sqlcon
     */

    function __construct($sqlcon) {
        $this->sqlcon = $sqlcon;
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


        return <<<body
<link rel="stylesheet" href="/assets/css/myself/page/search.css">
<pre id='langJson' style='display: none'>$jsonLang</pre>
<input type="text" id="getSearchInput" value='{$_POST["search"]}' hidden>
<div class="container mt-4">
    <div class="row row-cols-1 row-cols-md-4 g-4" id="getResult">
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

        $output = [];
        $searchInput = "%".$data['searchInput']."%";

        $stmt = $this->sqlcon->prepare("SELECT ID, review, state, name, summary, thumbnail, create_time, type FROM Event WHERE tag LIKE ?");
        $stmt->bind_param("s", $searchInput);
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
                'rate' => $rate['rate'],
                'total' => $rate['rate'],
                'comments' => $rate['comments'],
            );
        }

        return array(
            'code' => 200,
            'data' => $output,
        );
    }

    /**
     * @inheritDoc
     */
    public function path(): string {

        return '<li class="breadcrumb-item"><a href="/">' . showText("index.home") . '</a></li>'
            . '<li class="breadcrumb-item active">搜尋</li>';
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

        return "搜尋「".$_POST["search"]."」";
    }
}