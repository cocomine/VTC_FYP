<?php

namespace page;

use cocomine\IPage;
use mysqli;

class activity_view implements IPage
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
        $datatables_lang_url = showText('datatables_js.url');

        return <<<body
            <pre class="d-none" id="datatables_lang_url">$datatables_lang_url</pre>
            <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.2/css/dataTables.bootstrap5.min.css"/>
            <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.4.0/css/responsive.bootstrap5.min.css"/>
            <div class="container-sm">
                <div class="row">
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
        loadModules(['datatables.net', 'datatables.net-bs5', 'datatables.net-responsive', 'datatables.net-responsive-bs5', 'myself/page/activity_view'])
        </script>
        body;
    }

    /* POST請求 */
    function post(array $data): array {
        /* 檢視審核活動 */
        if(sizeof($this->upPath) > 0){
            return $this->reservePost->post($data);
        }

        global $auth;
        $output = array();

        /* 取得該用戶建立的活動給參與人數 */
        /* */
        $stmt = $this->sqlcon->prepare("SELECT ID, thumbnail, summary, name FROM Event WHERE UUID = ?");
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
            $temp = array(
                'ID' => $row['ID'],
                'thumbnail' => $row['thumbnail'],
                'summary' => $row['summary'],
                'name' => $row['name'],
                'plan' => null
            );

            /* get plan */
            $stmt->prepare("SELECT Event_ID, plan, (SELECT plan_name FROM Event_plan WHERE plan_ID = Event_schedule.plan) AS `plan_name` FROM Event_schedule WHERE Event_ID = ? ORDER BY LENGTH(plan_name)");
            $stmt->bind_param('i', $row['ID']);
            if (!$stmt->execute()) {
                return array(
                    'code' => 500,
                    'Title' => 'Database Error!',
                    'Message' => $stmt->error,
                );
            }

            /* get schedule */
            $schedule_result = $stmt->get_result();
            while ($row = $schedule_result->fetch_assoc()){
                $stmt->prepare("SELECT COALESCE(SUM(plan_people), 0) AS `total` FROM Book_event_plan WHERE event_schedule IN(SELECT Schedule_ID FROM Event_schedule WHERE plan = ? AND Event_ID = ?)");
                $stmt->bind_param('ii', $row['plan'], $row['Event_ID']);
                if (!$stmt->execute()) {
                    return array(
                        'code' => 500,
                        'Title' => 'Database Error!',
                        'Message' => $stmt->error,
                    );
                }

                /* get result */
                $schedule_row = $stmt->get_result()->fetch_assoc();
                $temp['plan'][] = array(
                    'plan_name' => $row['plan_name'],
                    'total' => $schedule_row['total']
                );
            }

            $output[] = $temp;
        }
        return array('data' => $output);

        return array();
    }

    /* path輸出 */
    function path(): string {
        return '<li class="breadcrumb-item active"><a href="/home">' . showText("index.home") . '</a></li>'." >  <li><span>查看預約</span></li>";
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