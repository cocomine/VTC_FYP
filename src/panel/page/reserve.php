<?php
/*
 * Copyright (c) 2022.
 * Create by cocomine
 */

namespace panel\page;

use cocomine\IPage;
use DateTime;
use DateTimeZone;
use mysqli;
use NumberFormatter;

class reserve implements IPage {

    private mysqli $sqlcon;

    /**
     * @param mysqli $sqlcon
     * @param array $upPath
     */
    public function __construct(mysqli $sqlcon, array $upPath) {
        $this->sqlcon = $sqlcon;
    }

    /**
     * @inheritDoc
     */
    public function access(bool $isAuth, int $role, bool $isPost): int {
        if (!$isAuth) return 401;
        if ($role < 1) return 403;
        return 200;
    }

    /**
     * @inheritDoc
     */
    public function showPage(): string {
        global $auth;
        $Text = showText('Reserve.Content');
        $fmt = numfmt_create('zh', NumberFormatter::DECIMAL);
        $time_zone = new DateTimeZone("Asia/Hong_Kong");
        $today = new DateTime('now', $time_zone);

        $stmt = $this->sqlcon->prepare(
            "SELECT f.ID, f.Flight, f.DateTime, f.`From`, f.`To`, r.Business, r.Economy, r.Meal, 
            (r.Business * p.Business + r.Economy * p.Economy) AS total 
        FROM Flight f, Reserve r, Price p WHERE f.ID = r.ID AND f.ID = p.ID AND r.UUID = ?");
        $stmt->bind_param('s', $auth->userdata['UUID']);
        if (!$stmt->execute()) return "";

        $result = $stmt->get_result();
        $table = "";
        while ($row = $result->fetch_assoc()) {
            //DateTime format
            $dateTime = DateTime::createFromFormat('Y-m-d H:i:s', $row['DateTime'], $time_zone);
            $row['DateTime'] = $dateTime->format('j M Y - g:i A');

            //Meal format
            $meal = $row['Meal'] ? '<span class="status-p bg-success"><i class="fa-solid fa-check"></i></span>' : '<span class="status-p bg-danger"><i class="fa-solid fa-xmark"></i></span>';

            //total format
            $row['total'] = $fmt->format($row['total']);

            //button disable
            $diff = $today->diff($dateTime);
            $disable = '';
            if (($diff->invert == 0 && $diff->days <= 3) || $diff->invert === 1) $disable = 'disabled';

            $table .=
                <<<table
                <tr>
                    <td>{$row['Flight']}</td>
                    <td>{$row['DateTime']}</td>
                    <td>{$row['From']}</td>
                    <td>{$row['To']}</td>
                    <td style="border-left: 1px solid rgba(153,153,153,0.3)">{$row['Business']}</td>
                    <td style="border-right: 1px solid rgba(153,153,153,0.3)">{$row['Economy']}</td>
                    <td>$meal</td>
                    <td>$ {$row['total']}</td>
                    <td>
                        <button type="button" class="btn btn-outline-primary btn-sm btn-rounded" data-bs-toggle="tooltip" data-bs-title="{$Text['Modify']}" data-action="edit" $disable data-id="{$row['ID']}"><i class="fa-solid fa-pen-to-square"></i></button>
                        <button type="button" class="btn btn-outline-danger btn-sm btn-rounded" data-bs-toggle="tooltip" data-bs-title="{$Text['Cancel']}" data-action="delete" $disable data-id="{$row['ID']}"><i class="fa-solid fa-plane-circle-xmark"></i></button>
                    </td>
                </tr>
                table;
        }

        /* json */
        $LangJson = json_encode(array(
            'Need_reserve' => $Text['Confirm_Reserve']['Need_reserve'],
            'No_Need_reserve' => $Text['Confirm_Reserve']['No_Need_reserve'],
        ));

        return <<<body
<pre id='LangJson' style='display: none'>$LangJson</pre>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.1/css/dataTables.bootstrap5.min.css"/>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.4.0/css/responsive.bootstrap5.min.css"/>
<div class="col-12 mt-4">
    <div class="card">
        <div class="card-body">
            <h4 class="header-title">{$Text['table'][0]}</h4>
            <div class="data-tables datatable-primary">
                <table id="dataTable" class="text-center w-100">
                    <thead class="text-capitalize">
                        <tr>
                            <th></th><th></th><th></th><th></th>
                            <th style="border-left: 1px solid rgba(153,153,153,0.3)">{$Text['table'][1]}</th>
                            <th style="border-right: 1px solid rgba(153,153,153,0.3)"></th>
                            <th></th><th></th><th></th>
                        </tr>
                        <tr>
                            <th>{$Text['table'][2]}</th>
                            <th>{$Text['table'][3]}</th>
                            <th>{$Text['table'][4]}</th>
                            <th>{$Text['table'][5]}</th>
                            <th style="border-left: 1px solid rgba(153,153,153,0.3)">{$Text['table'][6]}</th>
                            <th style="border-right: 1px solid rgba(153,153,153,0.3)">{$Text['table'][7]}</th>
                            <th>{$Text['table'][8]}</th>
                            <th>{$Text['table'][9]}</th>
                            <th>{$Text['table'][10]}</th>
                        </tr>
                    </thead>
                    <tbody>$table</tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="staticBackdropLabel">{$Text['editModal']['title']}</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>{$Text['editModal']['description']}</p>
                <div style="background-color: lightgray" class="rounded p-1">
                    <div class="row justify-content-sm-between align-items-center justify-content-center">
                        <h5 class="col-auto">{$Text['Cabin_type'][1]}</h5>
                        <div class="col-auto">
                            <div class="row align-items-center">
                                <div class="col-auto"><button type="button" class="btn btn-primary btn-rounded" data-reserve="Business-add"><i class="fa-solid fa-plus"></i></button></div>
                                <h6 class="col-auto" id="Business-count">0</h6>
                                <div class="col-auto"><button type="button" class="btn btn-outline-primary btn-rounded" data-reserve="Business-sub"><i class="fa-solid fa-minus"></i></button></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div style="background-color: lightgray" class="mt-2 rounded p-1">
                    <div class="row justify-content-sm-between align-items-center justify-content-center">
                        <h5 class="col-auto">{$Text['Cabin_type'][0]}</h4>
                        <div class="col-auto">
                            <div class="row align-items-center">
                                <div class="col-auto"><button type="button" class="btn btn-primary btn-rounded" data-reserve="Economy-add"><i class="fa-solid fa-plus"></i></button></div>
                                <h6 class="col-auto" id="Economy-count">0</h6>
                                <div class="col-auto"><button type="button" class="btn btn-outline-primary btn-rounded" data-reserve="Economy-sub"><i class="fa-solid fa-minus"></i></button></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div style="background-color: lightgray" class="mt-2 rounded p-1 py-2">
                    <div class="row justify-content-sm-between align-items-center justify-content-center">
                        <h5 class="col-auto">{$Text['reserve_meal']}</h4>
                        <div class="col-auto form-check form-switch">
                            <input class="form-check-input mt-0" style="font-size: 1.5rem" role="switch" type="checkbox" id="Meal">
                        </div>
                    </div>
                </div>
                <div class="row justify-content-between align-items-center mt-2 p-1">
                    <h4 class="col-auto" id="total">$ 0</h4>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary btn-rounded" id="Save" disabled><i class='fa-solid fa-save pe-2'></i>{$Text['editModal']['Save']}</button>
            </div>
        </div>
    </div>
</div>
<div id='Confirm-modal' class='modal fade' tabindex='-1'>
    <div class='modal-dialog modal-dialog-centered'>
        <div class='modal-content'>
            <div class='modal-header'>
                <h5 class='modal-title'><b>{$Text['Confirm_Reserve']['title']}</b></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class='modal-body'>
                <p>{$Text['Confirm_Reserve']['description']}</p>
                <div class="row gap-2 mx-2">
                    <div class="col text-center rounded p-1" style="background-color: lightgray">
                        <h4 id="Confirm-Business">5</h4>
                        <span>{$Text['Cabin_type'][1]}</span>
                    </div>
                    <div class="col text-center rounded p-1" style="background-color: lightgray">
                        <h4 id="Confirm-Economy">5</h4>
                        <span>{$Text['Cabin_type'][0]}</span>
                    </div>
                    <div class="col-12 rounded p-2" style="background-color: lightgray">
                        <h5 id="Confirm-meal"></h5>
                    </div>
                </div>
            </div>
            <div class='modal-footer'>
                <button class='btn btn-rounded btn-primary' id="confirm"><i class='fa-solid fa-plane-circle-check pe-2'></i>{$Text['Confirm_Reserve']['Confirm']}</button>
            </div>
        </div>
    </div>
</div>
<div id='Delete-modal' class='modal fade' tabindex='-1'>
    <div class='modal-dialog modal-dialog-centered'>
        <div class='modal-content'>
            <div class='modal-header'>
                <h5 class='modal-title'><b>{$Text['Delete_modal']['title']}</b></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class='modal-body'>
                <p>{$Text['Delete_modal']['description']}</p>
            </div>
            <div class='modal-footer'>
                <button class='btn btn-rounded btn-secondary' data-bs-dismiss="modal" aria-label="Close"><i class='fa-solid fa-arrow-left pe-2'></i>{$Text['Delete_modal']['Cancel']}</button>
                <button class='btn btn-rounded btn-danger' id="delete"><i class='fa-solid fa-plane-circle-xmark pe-2'></i>{$Text['Delete_modal']['Confirm']}</button>
            </div>
        </div>
    </div>
</div>
<script>
    require.config({
        paths:{
            'datatables.net': ['https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min'],
            'datatables.net-bs5': ['https://cdn.datatables.net/1.13.1/js/dataTables.bootstrap5.min'],
            'datatables.net-responsive': ['https://cdn.datatables.net/responsive/2.4.0/js/dataTables.responsive.min'],
            'datatables.net-responsive-bs5': ['https://cdn.datatables.net/responsive/2.4.0/js/responsive.bootstrap5'],
        },
    });
    loadModules(['datatables.net', 'datatables.net-bs5', 'datatables.net-responsive', 'datatables.net-responsive-bs5', 'myself/page/reserve'])
</script>
body;
    }

    /**
     * @inheritDoc
     */
    function post(array $data): array {
        global $auth;

        if($data['type'] === "info"){
            /* 取得詳細資料 */

            $stmt = $this->sqlcon->prepare(
                "SELECT f.Flight, r.Economy, r.Business, r.Meal, p.Business AS 'BusinessPrice', p.Economy AS 'EconomyPrice',
                (a.Economy - (SELECT IFNULL(SUM(Economy), 0) FROM Reserve WHERE ID = f.ID)) AS 'LastEconomy',
                (a.Business - (SELECT IFNULL(SUM(Business), 0) FROM Reserve WHERE ID = f.ID)) AS 'LastBusiness'
            FROM Reserve r, Flight f, Price p, Aircaft a
            WHERE r.ID = f.ID AND r.ID = p.ID AND f.Aircaft = a.ID AND r.UUID = ? AND r.ID = ?");
            $stmt->bind_param('si', $auth->userdata['UUID'], $data['data']['id']);
            if(!$stmt->execute()) return array(
                'code' => 400,
                'Title' => showText('Reserve.Content.Fail.0'),
                'Message' => showText('Reserve.Content.Fail.1')
            );

            // 處理資料
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $row['LastBusiness'] = max($row['LastBusiness'], 0);
            $row['LastEconomy'] = max($row['LastEconomy'], 0);
            return array(
                'code' => 200,
                'data' => $row
            );
        }else if($data['type'] === "edit"){
            /* 修改預約 */

            $stmt = $this->sqlcon->prepare("UPDATE Reserve SET Business = ?, Economy = ?, Meal = ? WHERE UUID = ? AND ID = ?");
            $stmt->bind_param('iiisi', $data['data']['Business'], $data['data']['Economy'], $data['data']['Meal'], $auth->userdata['UUID'], $data['data']['id']);
            if(!$stmt->execute()) return array(
                'code' => 400,
                'Title' => showText('Reserve.Content.Fail.0'),
                'Message' => showText('Reserve.Content.Fail.1')
            );

            //ok
            return array(
                'code' => 200,
                'Title' => showText("Reserve.Content.Success_Modify.0"),
                'Message' => showText("Reserve.Content.Success_Modify.1")
            );
        }else if($data['type'] === "delete"){
            /* 取消預約 */

            $stmt = $this->sqlcon->prepare("DELETE FROM Reserve WHERE UUID = ? AND ID = ?");
            $stmt->bind_param('si', $auth->userdata['UUID'], $data['data']['id']);
            if(!$stmt->execute()) return array(
                'code' => 400,
                'Title' => showText('Reserve.Content.Fail.0'),
                'Message' => showText('Reserve.Content.Fail.1')
            );

            //check
            if ($stmt->affected_rows >= 1) {
                return array(
                    'code' => 200,
                    'Title' => showText("Reserve.Content.Success_Delete.0"),
                    'Message' => showText("Reserve.Content.Success_Delete.1")
                );
            } else {
                return array(
                    'code' => 400,
                    'Title' => showText("Reserve.Content.Fail.0"),
                    'Message' => showText("Reserve.Content.Fail.1")
                );
            }
        }else{
            return array(
                'code' => 400,
                'Title' => showText('Reserve.Content.Fail.0'),
                'Message' => showText('Reserve.Content.Fail.1')
            );
        }
    }

    /**
     * @inheritDoc
     */
    function path(): string {
        return "<li><span><a href='/panel/'>" . showText("index.home") . "</a></span></li><li><span>" . showText("Reserve.Head") . "</span></li>";
    }

    /**
     * @inheritDoc
     */
    public function get_Title(): string {
        return showText("Reserve.Title");
    }

    /**
     * @inheritDoc
     */
    public function get_Head(): string {
        return showText("Reserve.Head");
    }
}