<?php

namespace page;

use cocomine\IPage;
use mysqli;


class reserve_view implements IPage
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
            <div class="container mt-4">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="data-tables datatable-primary">
                                    <table id="dataTable" class="w-100">
                                        <thead class="text-capitalize">
                                            <tr>
                                                <th>#</th>
                                                <th>活動</th>
                                                <th>活動計劃 / 人數</th>
                                                <th>預約時間</th>
                                                <th>活動總價錢</th>
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
        loadModules(['datatables.net', 'datatables.net-bs5', 'datatables.net-responsive', 'datatables.net-responsive-bs5', 'myself/page/reserveView'])
        </script>
        body;
    }

    /* POST請求 */
    public function post(array $data): array {

        global $auth;
        $output = array();
        $uuid = $auth->userdata['UUID'];

        /* 取得該用戶建立的活動給參與人數 */
        /* */
        $stmt = $this->sqlcon->prepare("SELECT b.ID AS 'BookID', e.ID AS 'EventID', e.thumbnail, e.summary, e.name, b.pay_price, b.book_date
            FROM Book_event b, Event e WHERE e.ID = b.event_ID AND b.User = ?");
        $stmt->bind_param('s', $uuid);
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
                'BookID' => $row['BookID'],
                'thumbnail' => $row['thumbnail'],
                'summary' => $row['summary'],
                'name' => $row['name'],
                'pay_price' => $row['pay_price'],
                'book_date' => $row['book_date'],
                'plan' => array(),
            );

            $stmt->prepare("SELECT p.plan_name, b.plan_people  FROM Book_event_plan b, Event_schedule e, Event_plan p 
                                    WHERE b.event_schedule = e.Schedule_ID AND e.plan = p.plan_ID AND b.Book_ID = ? AND e.Event_ID = ? AND p.Event_ID = ?");
            $stmt->bind_param('iii', $row['BookID'], $row['EventID'], $row['EventID']);
            if (!$stmt->execute()) {
                return array(
                    'code' => 500,
                    'Title' => 'Database Error!',
                    'Message' => $stmt->error,
                );
            }

            $plan_result = $stmt->get_result();
            while ($plan_row = $plan_result->fetch_assoc()){
                $temp['plan'][] = array(
                    'plan_name' => $plan_row['plan_name'],
                    'total' => $plan_row['plan_people']
                );
            }

            /*$stmt->prepare("SELECT Event_ID, plan_name FROM Event_plan WHERE Event_ID = ?");
            $stmt->bind_param('i', $row['ID']);
            if (!$stmt->execute()) {
                return array(
                    'code' => 500,
                    'Title' => 'Database Error!',
                    'Message' => $stmt->error,
                );
            }

            /* get pay_price Book_eventID
            $schedule_result = $stmt->get_result();
            while ($row = $schedule_result->fetch_assoc()){
                $stmt->prepare("SELECT event_ID, pay_price, Book_event.ID AS 'Book_eventID', order_datetime, Book_event_plan.plan_people AS `total` 
                    FROM Book_event, Book_event_plan 
                    WHERE event_ID = ? AND User = ? AND Book_event.ID = Book_event_plan.Book_ID");
                $stmt->bind_param('ii', $row['ID'],$uuid);
                if (!$stmt->execute()) {
                    return array(
                        'code' => 500,
                        'Title' => 'Database Error!',
                        'Message' => $stmt->error,
                    );
                }

                /* get result
                $schedule_row = $stmt->get_result()->fetch_assoc();
                $temp['plan'][] = array(
                    'plan_name' => $row['plan_name'],
                    'Book_eventID' => $schedule_row['Book_eventID'],
                    'order_datetime' => $schedule_row['order_datetime'],
                    'total' => $schedule_row['total']
                );


            }*/

            /*TEST*/

            /*END TEST*/

            $output[] = $temp;
        }
        return array('data' => $output);
    }

    /* path輸出 */
    function path(): string {
        return '<li class="breadcrumb-item active"><a href="/home">' . showText("index.home") . '</a></li>'.
            "<li class='breadcrumb-item active'><span>查看預約</span></li>";
    }

    /* 取得頁面標題 */
    public function get_Title(): string {
        return showText('查看預約');
    }

    /* 取得頁首標題 */
    public function get_Head(): string {
        return showText("查看預約");
    }
}