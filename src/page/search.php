<?php

namespace page;

use cocomine\IPage;
use mysqli;

class search implements IPage {
    private mysqli $sqlcon;
    private string $keyword;

    /**
     * search constructor.
     * sql連接
     * @param $sqlcon
     */

    function __construct($sqlcon) {
        $this->sqlcon = $sqlcon;
        $this->keyword = filter_var($_GET['search'], FILTER_SANITIZE_STRING);
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
        return <<<body
<link rel="stylesheet" href="/assets/css/myself/page/search.css">
<input type="text" id="getSearchInput" value='{$this->keyword}' hidden>
<div class="container mt-4">
    <div class="row g-4" id="getResult"></div>
</div>
body . <<<body
<script>
loadModules(['myself/page/search'])
</script>
body;

   }

    /**
     * @inheritDoc
     */
    public function post(array $data): array {

        $output = [];
        $searchInput = "%".$data['searchInput']."%";
        //$searchInput = "%"; //debug

        $stmt = $this->sqlcon->prepare("SELECT ID, review, state, name, summary, thumbnail, create_time, type FROM Event WHERE review = 1 AND state = 1 AND tag LIKE ? OR name LIKE ? OR summary LIKE ? ORDER BY create_time DESC");
        $stmt->bind_param("sss", $searchInput, $searchInput, $searchInput);
        if (!$stmt->execute()) {
            return array(
                'code' => 500,
                'Title' => showText('Error_Page.something_happened'),
                'Message' => $stmt->error,
            );
        }

        $rs = $stmt->get_result();
        while($row = $rs->fetch_assoc()) {
            $stmt->prepare("SELECT ROUND(SUM(r.rate)/COUNT(*), 1) AS 'rate', COUNT(*) AS 'comments' FROM Book_review r, Book_event b WHERE r.Book_ID = b.ID AND event_ID = ?");
            $stmt->bind_param("i", $row['ID']);
            if (!$stmt->execute()) {
                return array(
                    'code' => 500,
                    'Title' => showText('Error_Page.something_happened'),
                    'Message' => $stmt->error,
                );
            }

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
        return "搜尋 | X-Sport";
    }

    /**
     * @inheritDoc
     */
    public function get_Head(): string {

        return "搜尋「".$this->keyword."」";
    }
}