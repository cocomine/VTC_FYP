<?php
/*
 * Copyright (c) 2023.
 * Create by cocomine
 */

namespace panel\page;

use cocomine\IPage;
use mysqli;

class event implements IPage {

    private mysqli $sqlcon;

    public function __construct(mysqli $conn, array $upPath) {
        $this->sqlcon = $conn;
    }
    /**
     * @inheritDoc
     */
    public function access(bool $isAuth, int $role, bool $isPost): int {
        if (!$isAuth) return 401;
        if ($role < 2) return 403;
        return 200;
    }

    /**
     * @inheritDoc
     */
    public function showPage(): string {
        return <<<body
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.2/css/dataTables.bootstrap5.min.css"/>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.4.0/css/responsive.bootstrap5.min.css"/>
<div class="col-12">
    <div class="card">
        <div class="card-body">
            <div class="data-tables datatable-primary">
                <table id="dataTable" class="text-center w-100">
                    <thead class="text-capitalize">
                        <tr>
                            <th>活動標題</th>
                            <th>活動種類</th>
                            <th>標籤</th>
                            <th>狀態</th>
                            <th>審核狀態</th>
                            <th>發佈日期</th>
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
    loadModules(['datatables.net', 'datatables.net-bs5', 'datatables.net-responsive', 'datatables.net-responsive-bs5', 'myself/page/event/event'])
</script>
body;
    }

    /**
     * @inheritDoc
     */
    public function post(array $data): array {
        global $auth;

        /* 取得該用戶建立的活動 */
        $stmt = $this->sqlcon->prepare("SELECT review, state, type, tag, name, post_time FROM Event WHERE UUID = ?");
        $stmt->bind_param('s', $auth->userdata['UUID']);
        if (!$stmt->execute()) {
            return array(
                'code' => 500,
                'Title' => 'Database Error!',
                'Message' => $stmt->error,
            );
        }

        /* get result */
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()){

        }

        return array('data' => array(
            array('name' => 0,
                'type' => 1,
                'tag' => 2,
                'state' => 3,
                'review' => 4,
                'post_time' => 5)
        ));
    }

    /**
     * @inheritDoc
     */
    public function path(): string {
        return "<li><a href='/panel'>" . showText("index.home") . "</a></li>
            <li><span>活動</span></li>";
    }

    /**
     * @inheritDoc
     */
    public function get_Title(): string {
        return "活動 | X-Travel";
    }

    /**
     * @inheritDoc
     */
    public function get_Head(): string {
        return "活動";
    }
}