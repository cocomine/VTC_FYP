<?php
/*
 * Copyright (c) 2022.
 * Create by cocomine
 */

namespace panel\page;

use mysqli;

class account implements \cocomine\IPage {

    private mysqli $sqlcon;

    /**
     * @param mysqli $conn
     * @param array $upPath
     */
    public function __construct(mysqli $conn, array $upPath) {
        $this->sqlcon = $conn;
    }

    /**
     * @inheritDoc
     */
    public function access(bool $isAuth, int $role, bool $isPost): int {
        if ($isAuth && $role >= 2) return 200;
        return 403;
    }

    /**
     * @inheritDoc
     */
    public function showPage(): string {

        $Text = showText('ChangeSetting');//todo:
        return <<<body
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.1/css/dataTables.bootstrap5.min.css"/>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.4.0/css/responsive.bootstrap5.min.css"/>
<div class="col-12 mt-4">
    <div class="card">
        <div class="card-body">
            <h4 class="header-title">Create Account</h4>
            <form id='AddAC' novalidate class='needs-validation'>
                <div class="row g-2">
                    <div class='col-12 col-md-4'>
                        <label for='Name' class='col-form-label'>{$Text['Name']['Name']}</label>
                        <input class='form-control input-rounded' type='text' maxlength='16' id='Name' name='name' autocomplete='nickname' required>
                        <small class='form-text text-muted'>{$Text['Name']['limit']}</small>
                        <div class='invalid-feedback'>{$Text['Form']['Cant_EMPTY']}</div>
                    </div>
                    <div class='col-12 col-md-4'>
                        <label for='Email' class='col-form-label'>{$Text['Email']['Email']}</label>
                        <input class='form-control input-rounded' type='email' id='Email' name='email' autocomplete='email' required inputmode='email' pattern='^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$'>
                        <div class='invalid-feedback'>{$Text['Form']['Error_format']}</div>
                    </div>
                    <div class='col-12 col-md-4'>
                        <label class='col-form-label' for='Role'>Role</label>
                        <select class='input-rounded form-select' name='role' id='Role'>
                            <option value='1'>Normal user</option>
                            <option value='2'>Operator</option>
                            <option value='3'>Administrator</option>
                        </select>
                    </div>
                    <button type='submit' class='col-auto btn btn-rounded btn-primary mt-4 pr-4 pl-4 form-submit'><i class="fa-solid fa-plus me-2"></i>Create Account</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="col-12">
    <div class="card">
        <div class="card-body">
            <h4 class="header-title">Account List</h4>
            <div class="data-tables datatable-primary">
                <table id="dataTable" class="text-center w-100">
                    <thead class="text-capitalize">
                        <tr>
                            <th>User ID</th>
                            <th>User Name</th>
                            <th>User Email</th>
                            <th>Role</th>
                            <th>Last Login Time</th>
                            <th>Active state</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>123</td>
                            <td>123</td>
                            <td>123</td>
                            <td>123</td>
                            <td>123</td>
                            <td><span class="status-p bg-success">Activated</span></td>
                        </tr>
                        <tr>
                            <td>123</td>
                            <td>123</td>
                            <td>123</td>
                            <td>123</td>
                            <td>123</td>
                            <td><span class="status-p bg-danger">Not activated</span></td>
                        </tr>
                    </tbody>
                </table>
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
    loadModules(['datatables.net', 'datatables.net-bs5', 'datatables.net-responsive', 'datatables.net-responsive-bs5', 'myself/page/account'])
</script>
body;
    }

    /**
     * @inheritDoc
     */
    function post(array $data): array {
        return array();
    }

    /**
     * @inheritDoc
     */
    function path(): string {
        return "<li><span><a href='/panel/'>" . showText("index.home") . "</a></span></li><li><span>" . showText("Account.Head") . "</span></li>";
    }

    /**
     * @inheritDoc
     */
    public function get_Title(): string {
        return showText('Account.Title');
    }

    /**
     * @inheritDoc
     */
    public function get_Head(): string {
        return showText('Account.Head');
    }
}