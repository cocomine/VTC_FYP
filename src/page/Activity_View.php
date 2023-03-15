<?php

namespace page;

use cocomine\IPage;
use mysqli;

class Activity_View implements IPage
{

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
    public function showPage(): string {
        /* 檢視審核活動 */
        if(sizeof($this->upPath) > 0){
            return $this->reservePost->showPage();
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
                                        <th>活動計劃 / 預約人數</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        body;
    }

    /* POST請求 */
    function post(array $data): array {
        global $auth;

        return array();
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