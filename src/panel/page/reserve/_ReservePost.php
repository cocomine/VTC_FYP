<?php

namespace panel\page\reserve;

use mysqli;

class _ReservePost implements \cocomine\IPage {

    private mysqli $sqlcon;
    private array $upPath;
    private string $event_name;

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
            $stmt = $this->sqlcon->prepare("SELECT name, COUNT(ID) AS 'count' FROM Event WHERE ID = ? AND UUID = ?");
            $stmt->bind_param('ss', $this->upPath[0], $auth->userdata['UUID']);
            if (!$stmt->execute()) return 500;

            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            if ($row['count'] <= 0) return 403;
            $this->event_name = $row['name'];
        }
        return 200;
    }

    public function get_description(): ?string {
        return null;
    }

    public function get_image(): ?string {
        return null;
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
    <div class="row gy-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">已遞交預約客戶</h4>
                    <div class="alert alert-info"><i class="fa-solid fa-circle-info me-2"></i>選擇客戶查看資料</div>
                    <div class="data-tables datatable-primary">
                        <table id="dataTable" class="w-100">
                            <thead class="text-capitalize">
                                <tr>
                                    <th>#</th>
                                    <th>客戶</th>
                                    <th>預約日期</th>
                                    <th>活動計劃 / 預約人數</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">過去預約客戶</h4>
                    <div class="data-tables datatable-primary">
                        <table id="dataTable2" class="w-100">
                            <thead class="text-capitalize">
                                <tr>
                                    <th>#</th>
                                    <th>客戶</th>
                                    <th>預約日期</th>
                                    <th>活動計劃 / 預約人數</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
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
                    <div class="alert alert-warning" data-select><i class="fa-solid fa-triangle-exclamation me-2"></i>請先選擇客戶</div>
                    <div class="row gy-2" style="display: none" data-detail>
                        <div class="col-6">
                            <label class="form-label" for="lastname">姓氏</label>
                            <input type="text" class="form-control form-rounded" id="lastname" readonly>
                        </div>
                        <div class="col-6">
                            <label class="form-label" for="firstname">名字</label>
                            <input type="text" class="form-control form-rounded" id="firstname" readonly>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="email">電郵地址</label>
                            <input type="text" class="form-control form-rounded" id="email" readonly>
                        </div>
                        <div class="col-6">
                            <label class="form-label" for="country">國家 / 地區</label>
                            <select class="form-control form-rounded crs-country" id="country" readonly data-value="shortcode" data-default-option="請選擇" data-region-id="null" disabled style="background-color: initial"></select>
                        </div>
                        <div class="col-6">
                            <label class="form-label" for="phone">電話號碼</label>
                            <input type="text" class="form-control form-rounded" id="phone" readonly>
                        </div>
                        <div class="col-6">
                            <label class="form-label" for="sex">性別</label>
                            <input type="text" class="form-control form-rounded" id="sex" readonly>
                        </div>
                        <div class="col-6">
                            <label class="form-label" for="birth">出生日期</label>
                            <input type="text" class="form-control form-rounded" id="birth" readonly>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">預約詳細</h4>
                    <div class="alert alert-warning" data-select><i class="fa-solid fa-triangle-exclamation me-2"></i>請先選擇客戶</div>
                    <div style="display: none" data-detail>
                        <div class="col-12 my-2 border border-secondary border-opacity-50 border-2 rounded text-center">
                            <p>預約日期 (年/月/日)</p>
                            <code class="fs-3" id="reserve_date">000.000.000</code>
                        </div>
                        <div class="col-12">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead class="table-primary text-light" style="--bs-table-bg: var(--primary-color)">
                                        <tr>
                                            <th scope="col">活動計劃</th>
                                            <th scope="col">活動時段</th>
                                            <th scope="col">預約人數</th>
                                        </tr>
                                    </thead>
                                    <tbody id="reserve_detail"></tbody>
                                </table>
                            </div>
                        </div>
                        <div class="col-12">
                            <p class="text-secondary">下單預約時間: <span id="order_time">000.000.000</span></p>
                            <p class="text-secondary">預約編號: # <span id="order_id">00</span></p>
                            <p class="text-secondary">帳單編號: <a href="#" target="_blank"><span id="invoice_id">xxx</span></a></p>
                        </div>
                    </div>
                </div>
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
loadModules(['datatables.net', 'datatables.net-bs5', 'datatables.net-responsive', 'datatables.net-responsive-bs5', 'myself/page/reserve/_ReservePost', 'full.jquery.crs.min']);
</script>
body;
    }

    /**
     * @inheritDoc
     */
    public function post(array $data): array {
        global $auth;

        /* 展示用戶預約詳情 */
        if($_GET['type'] === "detail"){
            $stmt = $this->sqlcon->prepare("SELECT b.ID, u.Email, d.first_name, d.last_name, d.phone_code, d.phone, d.country, d.sex, d.birth, b.book_date, b.order_datetime, b.event_ID, b.invoice_number, b.invoice_url
                FROM Book_event b, User u, User_detail d WHERE b.ID = ? AND b.event_ID = ? AND b.User = u.UUID AND b.User = d.UUID");
            $stmt->bind_param('ss', $data['id'], $this->upPath[0]);
            if (!$stmt->execute()) {
                return array(
                    'code' => 500,
                    'Title' => 'Database Error!',
                    'Message' => $stmt->error,
                );
            }

            $result = $stmt->get_result();
            if($result->num_rows <= 0){  // no result
                return array(
                    'code' => 403,
                    'Title' => '該訂單不屬於這個活動',
                );
            }
            $output = $result->fetch_assoc(); //get result

            # 展示預約計劃
            $stmt->prepare("SELECT p.plan_name, b.plan_people, e.start_time, e.end_time FROM Book_event_plan b, Event_schedule e, Event_plan p 
                                    WHERE b.event_schedule = e.Schedule_ID AND e.plan = p.plan_ID AND b.Book_ID = ? AND e.Event_ID = ? AND p.Event_ID = ?");
            $stmt->bind_param('sss', $data['id'], $output['event_ID'], $output['event_ID']);
            if (!$stmt->execute()) {
                return array(
                    'code' => 500,
                    'Title' => 'Database Error!',
                    'Message' => $stmt->error,
                );
            }

            $output['plan'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); //get result

            return array('code' => 200, 'data' => $output);
        }

        /* 展示預約用戶列表 */
        $stmt = $this->sqlcon->prepare("SELECT b.ID, u.Name, d.last_name, d.first_name, b.book_date FROM Book_event b, User u, User_detail d WHERE b.event_ID = ? AND b.User = u.UUID AND b.User = d.UUID");
        $stmt->bind_param('s', $this->upPath[0]);
        if (!$stmt->execute()) {
            return array(
                'code' => 500,
                'Title' => showText('Error_Page.500_title'),
                'Message' => $stmt->error,
            );
        }

        /* 展示用戶預約計劃 */
        $output = array();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $tmp = array(
                'ID' => $row['ID'],
                'Name' => $row['Name'],
                'full_name' => $row['last_name'].' '.$row['first_name'],
                'book_date' => $row['book_date'],
                'plan' => array(),
            );

            $stmt->prepare("SELECT p.plan_name, b.plan_people FROM Book_event_plan b, Event_schedule e, Event_plan p 
                                    WHERE b.event_schedule = e.Schedule_ID AND e.plan = p.plan_ID AND b.Book_ID = ? AND e.Event_ID = ? AND p.Event_ID = ?");
            $stmt->bind_param('sss', $row['ID'], $this->upPath[0], $this->upPath[0]);
            if (!$stmt->execute()) {
                return array(
                    'code' => 500,
                    'Title' => showText('Error_Page.some_thing_happen'),
                    'Message' => $stmt->error,
                );
            }

            $plan_result = $stmt->get_result();
            while ($row = $plan_result->fetch_assoc()) {
                $tmp['plan'][] = array(
                    'plan_name' => $row['plan_name'],
                    'plan_people' => $row['plan_people'],
                );
            }

            $output[] = $tmp;
        }

        return array('data' => $output);
    }

    /**
     * @inheritDoc
     */
    public function path(): string {
        return "<li><a href='/panel'>" . showText("index.home") . "</a></li>
            <li><a href='/panel/reserve'>預約管理</a></li>
            <li><span>" . $this->event_name . "</span></li>";
    }

    /**
     * @inheritDoc
     */
    public function get_Title(): string {
        return $this->event_name." 預約管理 | X-Sport";
    }

    /**
     * @inheritDoc
     */
    public function get_Head(): string {
        return $this->event_name." 預約管理";
    }
}