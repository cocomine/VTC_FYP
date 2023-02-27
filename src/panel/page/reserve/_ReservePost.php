<?php

namespace panel\page\reserve;

use mysqli;

class _ReservePost implements \cocomine\IPage {

    private mysqli $sqlcon;
    private array $upPath;

    public function __construct(mysqli $conn, array $upPath) {
        $this->sqlcon = $conn;
        $this->upPath = $upPath;
    }

    /**
     * @inheritDoc
     */
    public function access(bool $isAuth, int $role, bool $isPost): int {
        global $auth;

        if (!$isAuth) return 401;
        if ($role < 2) return 403;

        //check the event is true owner
        if (sizeof($this->upPath) > 0 && preg_match("/[0-9]+/", $this->upPath[0])) {
            $stmt = $this->sqlcon->prepare("SELECT COUNT(ID) AS 'count' FROM Event WHERE ID = ? AND UUID = ?");
            $stmt->bind_param('ss', $this->upPath[0], $auth->userdata['UUID']);
            if (!$stmt->execute()) return 500;

            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            if ($row['count'] <= 0) return 403;
        }
        return 200;
    }

    /**
     * @inheritDoc
     */
    public function showPage(): string {

        $datatables_lang_url = showText('datatables_js.url');

        return <<<body
<pre class="d-none" id="datatables_lang_url">$datatables_lang_url</pre>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.2/css/dataTables.bootstrap5.min.css"/>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.4.0/css/responsive.bootstrap5.min.css"/>
<div class="col-12 col-lg-6">
    <div class="card">
        <div class="card-body">
            <h4 class="card-title">已遞交預約用戶</h4>
            <div class="alert alert-info"><i class="fa-solid fa-circle-info me-2"></i>選擇用戶查看資料</div>
            <div class="data-tables datatable-primary">
                <table id="dataTable" class="w-100">
                    <thead class="text-capitalize">
                        <tr>
                            <th>用戶</th>
                            <th>預約時間</th>
                            <th>活動計劃 / 預約人數</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<div class="col-12 col-lg-6">
    <div class="row gy-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">用戶資料</h4>
                    
                </div>
            </div>
        </div>
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">預約時段</h4>
                <div>
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
loadModules(['datatables.net', 'datatables.net-bs5', 'datatables.net-responsive', 'datatables.net-responsive-bs5', 'myself/page/reserve/_ReservePost']);
</script>
body;
    }

    /**
     * @inheritDoc
     */
    public function post(array $data): array {
        return array('data' => array());
    }

    /**
     * @inheritDoc
     */
    public function path(): string {
        return "<li><a href='/panel'>" . showText("index.home") . "</a></li>
            <li><a href='/panel/reserve'>預約管理</a></li>
            <li><span>活動 " . $this->upPath[0] . "</span></li>";
    }

    /**
     * @inheritDoc
     */
    public function get_Title(): string {
        return "預約管理 | X-Travel";
    }

    /**
     * @inheritDoc
     */
    public function get_Head(): string {
        return "預約管理";
    }
}