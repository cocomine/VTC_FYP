<?php
/*
 * Copyright (c) 2023.
 * Create by cocomine
 */

namespace panel\page;

use cocomine\IPage;
use mysqli;
use panel\page\review\_ReviewPost;

class review implements IPage {

    private mysqli $sqlcon;
    private array $upPath;
    private _ReviewPost $reviewPost;

    public function __construct(mysqli $conn, array $upPath) {
        $this->sqlcon = $conn;
        $this->upPath = $upPath;

        /* 檢視審核活動 */
        if(sizeof($this->upPath) > 0) $this->reviewPost = new _ReviewPost($conn, $upPath);
    }
    /**
     * @inheritDoc
     */
    public function access(bool $isAuth, int $role, bool $isPost): int {
        /* 檢視審核活動 */
        if(sizeof($this->upPath) > 0){
            return $this->reviewPost->access($isAuth, $role, $isPost);
        }

        if (!$isAuth) return 401;
        if ($role < 3) return 403;
        return 200;
    }

    /**
     * @inheritDoc
     */
    public function showPage(): string {
        /* 檢視審核活動 */
        if(sizeof($this->upPath) > 0){
            return $this->reviewPost->showPage();
        }

        $datatables_lang_url = showText('datatables_js.url');

        return <<<body
<pre class="d-none" id="datatables_lang_url">$datatables_lang_url</pre>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.2/css/dataTables.bootstrap5.min.css"/>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.4.0/css/responsive.bootstrap5.min.css"/>
<div class="col-12">
    <div class="card">
        <div class="card-body">
            <div class="data-tables datatable-primary">
                <table id="dataTable" class="w-100">
                    <thead class="text-capitalize">
                        <tr>
                            <th>活動</th>
                            <th>活動種類</th>
                            <th>發佈日期</th>
                            <th>狀態</th>
                            <th>審核狀態</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script>
    require.config({
        paths:{
            'datatables.net': ['https://cdn.datatables.net/1.13.2/js/jquery.dataTables.min'],
            'datatables.net-bs5': ['https://cdn.datatables.net/1.13.2/js/dataTables.bootstrap5.min'],
            'datatables.net-responsive': ['https://cdn.datatables.net/responsive/2.4.0/js/dataTables.responsive.min'],
            'datatables.net-responsive-bs5': ['https://cdn.datatables.net/responsive/2.4.0/js/responsive.bootstrap5'],
        },
    });
    loadModules(['datatables.net', 'datatables.net-bs5', 'datatables.net-responsive', 'datatables.net-responsive-bs5', 'myself/page/review/review'])
</script>
body;
    }

    /**
     * @inheritDoc
     */
    public function post(array $data): array {
        /* 檢視審核活動 */
        if(sizeof($this->upPath) > 0){
            return $this->reviewPost->post($data);
        }

        global $auth;

        /* 取得該用戶建立的活動 */
        $stmt = $this->sqlcon->prepare("SELECT ID, thumbnail, summary, review, state, type, name, post_time FROM Event WHERE state >= 0 AND review != 1");
        if (!$stmt->execute()) {
            return array(
                'code' => 500,
                'Title' => showText('Error_Page.500_title'),
                'Message' => $stmt->error,
            );
        }

        /* get result */
        $result = $stmt->get_result();

        $data = array();
        while ($row = $result->fetch_assoc()){
            $data[] = $row;
        }

        return array('data' => $data);
    }

    /**
     * @inheritDoc
     */
    public function path(): string {
        /* 檢視審核活動 */
        if(sizeof($this->upPath) > 0){
            return $this->reviewPost->path();
        }

        return "<li><a href='/panel'>" . showText("index.home") . "</a></li>
            <li><span>審核活動</span></li>";
    }

    /**
     * @inheritDoc
     */
    public function get_Title(): string {
        /* 檢視審核活動 */
        if(sizeof($this->upPath) > 0){
            return $this->reviewPost->get_Title();
        }

        return "審核活動 | X-Sport";
    }

    /**
     * @inheritDoc
     */
    public function get_Head(): string {
        /* 檢視審核活動 */
        if(sizeof($this->upPath) > 0){
            return $this->reviewPost->get_Head();
        }

        return "審核活動";
    }
}